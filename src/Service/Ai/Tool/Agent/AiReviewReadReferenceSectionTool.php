<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai\Tool\Agent;

use DR\Review\Entity\Ai\AiReviewReferenceSection;
use DR\Review\Repository\Ai\AiReviewReferenceSectionRepository;
use DR\Review\Service\Ai\Reference\AiReviewReferenceBudgetTracker;
use Mcp\Exception\ToolCallException;
use Psr\Log\LoggerInterface;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;
use Symfony\AI\Platform\Contract\JsonSchema\Attribute\Schema;

#[AsTool(
    'read_review_reference_section',
    'Reads the content of one AI review reference section by id (obtained via list_review_references) for the given code review and ' .
    'file. Each file review has a limited cumulative budget for reference content; once exhausted, further reads will be truncated or refused.'
)]
class AiReviewReadReferenceSectionTool
{
    public function __construct(
        private ?LoggerInterface $aiLogger,
        private readonly AiReviewReferenceSectionRepository $sectionRepository,
        private readonly AiReviewReferenceBudgetTracker $budgetTracker
    ) {
    }

    public function __invoke(
        #[Schema(description: 'The CODE_REVIEW_ID of the review', minimum: 1)] int $codeReviewId,
        #[Schema(description: 'The file path currently being reviewed')] string $filepath,
        #[Schema(description: 'The sectionId returned by list_review_references', minimum: 1)] int $sectionId
    ): string {
        $section = $this->sectionRepository->find($sectionId);
        if ($section === null) {
            throw new ToolCallException('Reference section not found: ' . $sectionId);
        }

        $sessionKey = $codeReviewId . ':' . $filepath;
        $remaining  = $this->budgetTracker->remaining($sessionKey);
        if ($remaining <= 0) {
            return 'Reference budget exhausted for this file review; no further reference sections can be read.';
        }

        $content = $section->getContent();
        if (strlen($content) > AiReviewReferenceSection::MAX_CONTENT_LENGTH) {
            $content = substr(
                $content,
                0,
                AiReviewReferenceSection::MAX_CONTENT_LENGTH
            ) . "\n\n[...truncated: section exceeds maximum allowed length...]";
        }
        if (strlen($content) > $remaining) {
            $content = substr($content, 0, $remaining) . "\n\n[...truncated: per-file reference budget exhausted...]";
        }

        $this->aiLogger?->info(
            'AiReviewReadReferenceSectionTool: Reading section {sectionId} for "{filepath}" in review {id}',
            ['id' => $codeReviewId, 'filepath' => $filepath, 'sectionId' => $sectionId]
        );

        $this->budgetTracker->consume($sessionKey, strlen($content));

        return $content;
    }
}

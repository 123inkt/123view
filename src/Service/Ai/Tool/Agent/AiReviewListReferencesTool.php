<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai\Tool\Agent;

use DR\Review\Entity\Ai\AiReviewReferenceSection;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Exception\Ai\CodeReviewNotFoundException;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Ai\Reference\AiReviewReferenceMatcherService;
use DR\Review\Service\CodeReview\CodeReviewDiffService;
use Psr\Log\LoggerInterface;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;
use Symfony\AI\Platform\Contract\JsonSchema\Attribute\Schema;
use Throwable;

#[AsTool(
    'list_review_references',
    'Lists AI review reference guidance applicable to the given file in this code review: a short heading, its owning reference name, and its ' .
    'character length. Call read_review_reference_section afterwards with a sectionId to fetch a specific section\'s content. Only sections that ' .
    'are actually relevant to this file are returned; there is no need to call this more than once per file.'
)]
class AiReviewListReferencesTool
{
    public function __construct(
        private ?LoggerInterface $aiLogger,
        private readonly CodeReviewRepository $repository,
        private readonly CodeReviewDiffService $diffService,
        private readonly AiReviewReferenceMatcherService $matcherService,
    ) {
    }

    /**
     * @return list<array{sectionId: int, reference: string, heading: string, characters: int<0, max>}>
     * @throws Throwable
     */
    public function __invoke(
        #[Schema(description: 'The CODE_REVIEW_ID of the review', minimum: 1)] int $codeReviewId,
        #[Schema(description: 'The file path currently being reviewed')] string $filepath
    ): array {
        $review = $this->repository->find($codeReviewId);
        if ($review === null) {
            throw new CodeReviewNotFoundException($codeReviewId);
        }

        $this->aiLogger?->info(
            'AiReviewListReferencesTool: Listing references for "{filepath}" in review {id}',
            ['id' => $codeReviewId, 'filepath' => $filepath]
        );

        $diffContent = $this->findDiffContent($review, $filepath);
        $sections    = $this->matcherService->getApplicableSections($filepath, $diffContent);

        return array_values(array_map(
            static fn(AiReviewReferenceSection $section): array => [
                'sectionId'  => $section->getId(),
                'reference'  => $section->getReference()->getName(),
                'heading'    => $section->getHeading(),
                'characters' => strlen($section->getContent()),
            ],
            $sections
        ));
    }

    /**
     * @throws Throwable
     */
    private function findDiffContent(CodeReview $review, string $filepath): string
    {
        foreach ($this->diffService->getDiff($review) as $file) {
            if ($file->getPathname() === $filepath) {
                return (string)$file->raw;
            }
        }

        return '';
    }
}

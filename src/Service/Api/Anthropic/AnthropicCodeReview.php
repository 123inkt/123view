<?php
declare(strict_types=1);

namespace DR\Review\Service\Api\Anthropic;

use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Throwable;

class AnthropicCodeReview
{
    use ClockAwareTrait;

    private const array DISALLOWED_EXTENSIONS = ['lock', 'json'];

    public function __construct(
        #[Autowire(env: 'ANTHROPIC_COMMENT_USER_ID')] private readonly int $userId,
        private readonly LoggerInterface $claudeLogger,
        private readonly ReviewDiffServiceInterface $diffService,
        private readonly AnthropicPromptService $promptService,
        private readonly CodeReviewRevisionService $revisionService,
        private readonly CodeReviewRepository $reviewRepository,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function requestCodeReview(CodeReview $review): void
    {
        // gather revisions
        $revisions = $this->revisionService->getRevisions($review);

        // get diff files for review
        $files = $this->diffService->getDiffForRevisions(
            $review->getRepository(),
            $revisions,
            new FileDiffOptions(
                10,
                DiffComparePolicy::IGNORE_EMPTY_LINES,
                includeRaw: true
            )
        );

        // filter out large and non-essential files
        $files = array_filter($files, static function (DiffFile $file) {
            if (str_contains($file->getPathname(), 'baseline')) {
                return false;
            }
            if (in_array(strtolower((string)$file->getFile()?->getExtension()), self::DISALLOWED_EXTENSIONS, true)) {
                return false;
            }
            if ($file->isDeleted()) {
                return false;
            }
            if (count($file->getLines()) > 500) {
                return false;
            }

            return true;
        });
        if (count($files) === 0) {
            $this->claudeLogger->info('No suitable files found for code review, skipping review {reviewId}', ['reviewId' => $review->getId()]);

            return;
        }

        // get the diffs
        $diffs = array_map(fn(DiffFile $file) => $file->raw, $files);

        // execute the prompts
        $result = $this->promptService->prompt($review->getRepository(), implode("\n", $diffs));

        $this->claudeLogger->info('Code review response {response}', ['response' => $result, 'reviewId' => $review->getId()]);

        $review->setAiReview($result);
        $this->reviewRepository->save($review, true);
    }
}

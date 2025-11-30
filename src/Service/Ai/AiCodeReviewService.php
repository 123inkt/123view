<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai;

use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\Component\Clock\ClockAwareTrait;
use Throwable;

class AiCodeReviewService
{
    use ClockAwareTrait;

    public const int RESULT_NO_FILES = 1;
    public const int RESULT_SUCCESS  = 2;
    public const int RESULT_FAILURE  = 3;

    private const array DISALLOWED_EXTENSIONS = ['lock', 'json'];

    public function __construct(
        private ?LoggerInterface $aiLogger,
        private readonly ReviewDiffServiceInterface $diffService,
        private readonly CodeReviewRevisionService $revisionService,
        private readonly AgentInterface $agent,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function startCodeReview(CodeReview $review): int
    {
        // gather revisions
        $revisions = $this->revisionService->getRevisions($review);

        // get diff files for review
        $files = $this->diffService->getDiffForRevisions(
            $review->getRepository(),
            $revisions,
            new FileDiffOptions(5, DiffComparePolicy::IGNORE_EMPTY_LINES, includeRaw: true)
        );

        // filter out large and non-essential files
        $files = array_filter($files, static function (DiffFile $file) {
            if (str_contains($file->getPathname(), 'baseline')) {
                return false;
            }
            if (in_array(strtolower((string)$file->getFile()?->getExtension()), self::DISALLOWED_EXTENSIONS, true)) {
                return false;
            }
            if ($file->binary || $file->isDeleted()) {
                return false;
            }

            return count($file->getLines()) <= 500;
        });
        if (count($files) === 0) {
            $this->aiLogger?->info('No suitable files found for code review, skipping review {reviewId}', ['reviewId' => $review->getId()]);

            return self::RESULT_NO_FILES;
        }

        // get the diffs
        $diff = implode("\n", array_map(static fn(DiffFile $file) => $file->raw, $files));
        $message = "CODE_REVIEW_ID: " . $review->getId() . "\n";

        $this->aiLogger?->info(
            'AiCodeReviewService: Starting code review for review {id} with {fileCount} files',
            ['id' => $review->getId(), 'fileCount' => count($files),]
        );

        // invoke the agent
        try {
            $this->agent->call(new MessageBag(Message::ofUser($message . $diff)));
        } catch (Throwable $exception) {
            $this->aiLogger?->error($exception->getMessage(), ['exception' => $exception]);

            return self::RESULT_FAILURE;
        }

        return self::RESULT_SUCCESS;
    }
}

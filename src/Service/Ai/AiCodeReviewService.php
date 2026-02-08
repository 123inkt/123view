<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai;

use DR\Review\Doctrine\Type\CodeReviewType;
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

    public function __construct(
        private ?LoggerInterface $aiLogger,
        private readonly ReviewDiffServiceInterface $diffService,
        private readonly CodeReviewRevisionService $revisionService,
        private readonly AgentInterface $agent,
        private readonly AiCodeReviewFileFilter $fileFilter,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function startCodeReview(CodeReview $review): int
    {
        $options = new FileDiffOptions(5, DiffComparePolicy::IGNORE_EMPTY_LINES, includeRaw: true);

        // gather files for review  revisions
        if ($review->getType() === CodeReviewType::BRANCH) {
            $files = $this->diffService->getDiffForBranch($review, [], (string)$review->getReferenceId(), $options);
        } else {
            $files = $this->diffService->getDiffForRevisions($review->getRepository(), $this->revisionService->getRevisions($review), $options);
        }

        // filter out large and non-essential files
        $files = array_filter($files, $this->fileFilter);
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

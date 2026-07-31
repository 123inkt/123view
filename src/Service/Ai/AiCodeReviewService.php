<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Service\Ai\Reference\AiReviewReferenceBudgetTracker;
use DR\Review\Service\CodeReview\CodeReviewDiffService;
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

    /** Some files were reviewed successfully, but at least one was skipped for size or failed. */
    public const int RESULT_PARTIAL = 4;

    public function __construct(
        private ?LoggerInterface $aiLogger,
        private readonly CodeReviewDiffService $diffService,
        private readonly AgentInterface $agent,
        private readonly AiCodeReviewFileFilter $fileFilter,
        private readonly AiReviewInstructionProvider $instructionProvider,
        private readonly AiReviewReferenceBudgetTracker $budgetTracker,
        private readonly AiCodeReviewCoverageReporter $coverageReporter,
    ) {
    }

    /**
     * Reviews the given code review one changed file at a time, using a fresh agent call per file
     * so the model's context window is never shared across files. Continues past individual
     * failures so a single bad file cannot prevent the rest of the review from completing.
     *
     * @throws Throwable
     */
    public function startCodeReview(CodeReview $review): int
    {
        [$accepted, $skippedForSize] = $this->partitionFiles($this->diffService->getDiff($review));

        if (count($accepted) === 0 && count($skippedForSize) === 0) {
            $this->aiLogger?->info('No suitable files found for code review, skipping review {reviewId}', ['reviewId' => $review->getId()]);

            return self::RESULT_NO_FILES;
        }

        $this->aiLogger?->info(
            'AiCodeReviewService: Starting code review for review {id} with {fileCount} files ({skippedCount} skipped for size)',
            ['id' => $review->getId(), 'fileCount' => count($accepted), 'skippedCount' => count($skippedForSize)]
        );

        $systemPrompt = $this->instructionProvider->getSystemPrompt();
        $failedFiles  = [];
        $reviewedCount = 0;

        foreach ($accepted as $file) {
            $filepath   = $file->getPathname();
            $sessionKey = $review->getId() . ':' . $filepath;

            $this->budgetTracker->startSession($sessionKey);
            try {
                $this->reviewFile($review, $file, $systemPrompt);
                $reviewedCount++;
            } catch (Throwable $exception) {
                $this->aiLogger?->error(
                    'AiCodeReviewService: Failed to review file "{filepath}" in review {id}: {message}',
                    ['id' => $review->getId(), 'filepath' => $filepath, 'message' => $exception->getMessage(), 'exception' => $exception]
                );
                $failedFiles[] = $filepath;
            } finally {
                $this->budgetTracker->endSession($sessionKey);
            }
        }

        if (count($failedFiles) > 0 || count($skippedForSize) > 0) {
            $this->coverageReporter->reportIncompleteCoverage($review, $skippedForSize, $failedFiles, $reviewedCount);
        }

        if ($reviewedCount === 0) {
            return count($accepted) === 0 ? self::RESULT_NO_FILES : self::RESULT_FAILURE;
        }

        if (count($failedFiles) > 0 || count($skippedForSize) > 0) {
            return self::RESULT_PARTIAL;
        }

        return self::RESULT_SUCCESS;
    }

    /**
     * @param DiffFile[] $files
     *
     * @return array{0: DiffFile[], 1: string[]} accepted files, and paths skipped due to size limits
     */
    private function partitionFiles(array $files): array
    {
        $accepted       = [];
        $skippedForSize = [];

        foreach ($files as $file) {
            $reason = $this->fileFilter->getSkipReason($file);
            if ($reason === null) {
                $accepted[] = $file;
                continue;
            }
            if ($reason->isCoverageGap()) {
                $skippedForSize[] = $file->getPathname();
            }
        }

        return [$accepted, $skippedForSize];
    }

    /**
     * @throws Throwable
     */
    private function reviewFile(CodeReview $review, DiffFile $file, string $systemPrompt): void
    {
        $filepath = $file->getPathname();

        $this->aiLogger?->info(
            'AiCodeReviewService: Reviewing file "{filepath}" in review {id}',
            ['id' => $review->getId(), 'filepath' => $filepath]
        );

        $userMessage = 'CODE_REVIEW_ID: ' . $review->getId() . "\nFILE: " . $filepath . "\n\n" . $file->raw;
        $messages    = new MessageBag(Message::forSystem($systemPrompt), Message::ofUser($userMessage));

        $this->agent->call($messages);
    }
}

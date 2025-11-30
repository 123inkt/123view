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

    private const array DISALLOWED_EXTENSIONS = ['lock', 'json'];

    public function __construct(
        private readonly LoggerInterface $claudeLogger,
        private readonly ReviewDiffServiceInterface $diffService,
        private readonly CodeReviewRevisionService $revisionService,
        private readonly AgentInterface $agent,
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
        $diff = implode("\n", array_map(fn(DiffFile $file) => $file->raw, $files));
        $message = "CODE_REVIEW_ID: " . $review->getId() . "\n";

        // invoke the agent
        $this->agent->call(new MessageBag(Message::ofUser($message . $diff)));
    }
}

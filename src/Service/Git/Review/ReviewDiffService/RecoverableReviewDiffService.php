<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Review\ReviewDiffService;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Service\CodeReview\Branch\BranchReviewTargetBranchService;
use DR\Review\Service\Git\Review\FileDiffOptions;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Process\Exception\ProcessFailedException;

class RecoverableReviewDiffService implements ReviewDiffServiceInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly BranchReviewTargetBranchService $targetBranchService,
        private readonly ReviewDiffServiceInterface $diffService
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getDiffForRevisions(Repository $repository, array $revisions, ?FileDiffOptions $options = null): array
    {
        return $this->diffService->getDiffForRevisions($repository, $revisions, $options);
    }

    /**
     * The target branch of the review might be stale, try to reset it to the main branch if the diff fails.
     * @inheritDoc
     */
    public function getDiffForBranch(CodeReview $review, array $revisions, string $branchName, ?FileDiffOptions $options = null): array
    {
        try {
            return $this->getDiffForBranch($review, $revisions, $branchName, $options);
        } catch (ProcessFailedException $exception) {
            if ($review->getTargetBranch() === $review->getRepository()->getMainBranchName()) {
                throw $exception;
            }

            $this->logger->notice('Failed to get diff for branch, trying to reset target branch', ['exception' => $exception]);
            $review->setTargetBranch($review->getRepository()->getMainBranchName());
        }

        return $this->diffService->getDiffForBranch($review, $revisions, $branchName, $options);
    }
}

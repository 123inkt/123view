<?php
declare(strict_types=1);

namespace DR\Review\Service\Api\Gitlab;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\Revision\Revision;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ReviewApprovalValidatorService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(#[Autowire(env: 'GITLAB_REVIEWER_SYNC_BRANCH_PATTERN')] private readonly string $branchPattern)
    {
    }

    /**
     * @phpstan-assert-if-true CodeReview $review
     * @phpstan-assert-if-true CodeReviewer $reviewer
     * @phpstan-assert-if-true int $projectId
     */
    public function validate(?CodeReview $review, ?CodeReviewer $reviewer, ?int $projectId): bool
    {
        if ($review === null || $reviewer === null || $projectId === null) {
            $this->logger?->info('ReviewerStateChange: Gitlab reviewer sync skipped as review, reviewer or projectId not found');

            return false;
        }

        if ($review->getRepository()->isGitApprovalSync() === false) {
            $this->logger?->info(
                'ReviewerStateChange: Gitlab reviewer sync skipped as repository has approvals disabled',
                ['reviewId' => $review->getId(), 'reviewerId' => $reviewer->getId(), 'projectId' => $projectId]
            );

            return false;
        }

        $remoteRef = $review->getRevisions()->findFirst(static fn($key, Revision $value) => $value->getFirstBranch() !== null)?->getFirstBranch();
        if ($remoteRef === null || preg_match($this->branchPattern, $remoteRef) !== 1) {
            $this->logger?->info(
                'ReviewerStateChange: Remote ref for review {id} is {ref}, but doesn\'t match pattern {pattern}',
                ['id' => $review->getId(), 'ref' => $remoteRef, 'pattern' => $this->branchPattern]
            );

            return false;
        }

        return true;
    }
}

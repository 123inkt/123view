<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler\Gitlab;

use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Message\Reviewer\ReviewerStateChanged;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Api\Gitlab\ReviewApprovalService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

class ReviewerStateChangeMessageHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly bool $gitlabReviewerSyncEnabled,
        private readonly string $branchPattern,
        private readonly CodeReviewRepository $reviewRepository,
        private readonly ReviewApprovalService $reviewApprovalService
    ) {
    }

    /**
     * @throws Throwable
     */
    #[AsMessageHandler(fromTransport: 'async_messages')]
    public function __invoke(ReviewerStateChanged $event): void
    {
        if ($this->gitlabReviewerSyncEnabled === false) {
            $this->logger?->info('ReviewerStateChange: Gitlab reviewer sync disabled. Review id: {id}', ['id' => $event->reviewId]);

            return;
        }

        $review    = $this->reviewRepository->find($event->reviewId);
        $reviewer  = $review?->getReviewers()->findFirst(static fn($key, $reviewer) => $reviewer->getId() === $event->reviewerId);
        $projectId = $review?->getRepository()->getRepositoryProperty('gitlab-project-id');
        if ($review === null || $reviewer === null || $projectId === null) {
            $this->logger?->info(
                'ReviewerStateChange: Gitlab reviewer sync skipped as review, reviewer or projectId not found',
                ['reviewId' => $event->reviewId, 'reviewerId' => $event->reviewerId, 'projectId' => $projectId,]
            );

            return;
        }

        if ($review->getRepository()->isGitApprovalSync() === false) {
            $this->logger?->info(
                'ReviewerStateChange: Gitlab reviewer sync skipped as repository has approvals disabled',
                ['reviewId' => $event->reviewId, 'reviewerId' => $event->reviewerId, 'projectId' => $projectId,]
            );

            return;
        }

        $remoteRef = $review->getRevisions()->findFirst(static fn($key, Revision $value) => $value->getFirstBranch() !== null)?->getFirstBranch();
        if ($remoteRef === null || preg_match($this->branchPattern, $remoteRef) !== 1) {
            $this->logger?->info(
                'ReviewerStateChange: Remote ref for review {id} is {ref}, but doesn\'t match pattern {pattern}',
                ['id' => $review->getId(), 'ref' => $remoteRef, 'pattern' => $this->branchPattern]
            );

            return;
        }

        if ($event->newState === CodeReviewerStateType::ACCEPTED) {
            $this->reviewApprovalService->approve($review, $reviewer, true);
        } elseif ($event->oldState === CodeReviewerStateType::ACCEPTED) {
            $this->reviewApprovalService->approve($review, $reviewer, false);
        }
    }
}

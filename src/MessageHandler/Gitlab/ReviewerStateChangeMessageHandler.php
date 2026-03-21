<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler\Gitlab;

use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Message\Reviewer\ReviewerStateChanged;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Api\Gitlab\ReviewApprovalService;
use DR\Review\Service\Api\Gitlab\ReviewApprovalValidatorService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

class ReviewerStateChangeMessageHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly bool $gitlabReviewerSyncEnabled,
        private readonly CodeReviewRepository $reviewRepository,
        private readonly ReviewApprovalValidatorService $reviewApprovalValidatorService,
        private readonly ReviewApprovalService $reviewApprovalService,
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

        $review     = $this->reviewRepository->find($event->reviewId);
        $reviewer   = $review?->getReviewers()->findFirst(static fn($key, $reviewer) => $reviewer->getId() === $event->reviewerId);
        $projectId  = $review?->getRepository()->getRepositoryProperty('gitlab-project-id');
        $projectIdInt = $projectId !== null ? (int)$projectId : null;
        if ($this->reviewApprovalValidatorService->validate($review, $reviewer, $projectIdInt) === false) {
            return;
        }

        if ($event->newState === CodeReviewerStateType::ACCEPTED) {
            $this->reviewApprovalService->approve($review, $reviewer, true);
        } elseif ($event->oldState === CodeReviewerStateType::ACCEPTED) {
            $this->reviewApprovalService->approve($review, $reviewer, false);
        }
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler\Gitlab;

use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Message\Reviewer\ReviewerStateChanged;
use DR\Review\Repository\Review\CodeReviewerRepository;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Api\Gitlab\GitlabApiProvider;
use DR\Review\Service\Api\Gitlab\ReviewMergeRequestService;
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
        private readonly CodeReviewerRepository $reviewerRepository,
        private readonly GitlabApiProvider $apiProvider,
        private readonly ReviewMergeRequestService $mergeRequestService
    ) {
    }

    /**
     * @throws Throwable
     */
    #[AsMessageHandler(fromTransport: 'async_messages')]
    public function __invoke(ReviewerStateChanged $event): void
    {
        if ($this->gitlabReviewerSyncEnabled === false) {
            $this->logger?->info('Gitlab reviewer sync disabled. Reviewer id: {id}', ['id' => $event->reviewId]);

            return;
        }

        $review    = $this->reviewRepository->find($event->reviewId);
        $reviewer  = $review?->getReviewers()->findFirst(static fn($key, $reviewer) => $reviewer->getId() === $event->reviewerId);
        $projectId = $review?->getRepository()->getRepositoryProperty('gitlab-project-id');
        if ($review === null || $reviewer === null || $projectId === null) {
            $this->logger?->info(
                'Gitlab reviewer sync skipped as review, reviewer or projectId not found',
                [
                    'reviewId'   => $event->reviewId,
                    'reviewerId' => $event->reviewerId,
                    'projectId'  => $projectId,
                ]
            );

            return;
        }

        $api = $this->apiProvider->create($review->getRepository(), $reviewer->getUser());
        if ($api === null) {
            $this->logger?->info('No api configuration found for reviewer {id}', ['id' => $reviewer->getId()]);

            return;
        }

        $mergeRequestIId = $this->mergeRequestService->retrieveMergeRequestIID($api, $review);
        if ($mergeRequestIId === null) {
            $this->logger?->info('No mergeRequestIdd found for review {id}', ['id' => $review->getId()]);

            return;
        }

        if ($event->newState === CodeReviewerStateType::ACCEPTED) {
            $api->mergeRequests()->approve((int)$projectId, $mergeRequestIId);
        } elseif ($event->oldState === CodeReviewerStateType::ACCEPTED) {
            $api->mergeRequests()->unapprove((int)$projectId, $mergeRequestIId);
        }
    }
}

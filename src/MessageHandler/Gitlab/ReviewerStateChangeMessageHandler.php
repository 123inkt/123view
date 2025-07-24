<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler\Gitlab;

use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Message\Reviewer\ReviewerStateChanged;
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
        private readonly string $branchPattern,
        private readonly CodeReviewRepository $reviewRepository,
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
            $this->logger?->info('ReviewerStateChange: Gitlab reviewer sync disabled. Reviewer id: {id}', ['id' => $event->reviewId]);

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

        $remoteRef = $review->getRevisions()->findFirst(static fn($key, Revision $value) => $value->getFirstBranch() !== null)?->getFirstBranch();
        if ($remoteRef === null || preg_match($this->branchPattern, $remoteRef) !== 1) {
            $this->logger?->info(
                'ReviewerStateChange: Remote ref for review {id} is {ref}, but doesn\'t match pattern {pattern}',
                ['id' => $review->getId(), 'ref' => $remoteRef, 'pattern' => $this->branchPattern]
            );

            return;
        }

        $api = $this->apiProvider->create($review->getRepository(), $reviewer->getUser());
        if ($api === null) {
            $this->logger?->info('ReviewerStateChange: No api configuration found for reviewer {id}', ['id' => $reviewer->getId()]);

            return;
        }

        $mergeRequestIId = $this->mergeRequestService->retrieveMergeRequestIID($api, $review);
        if ($mergeRequestIId === null) {
            $this->logger?->info('ReviewerStateChange: No mergeRequestIdd found for review {id}', ['id' => $review->getId()]);

            return;
        }

        if ($event->newState === CodeReviewerStateType::ACCEPTED) {
            $this->logger?->info('ReviewerStateChange: Approving merge request {id}', ['id' => $mergeRequestIId]);
            $api->mergeRequests()->approve((int)$projectId, $mergeRequestIId);
        } elseif ($event->oldState === CodeReviewerStateType::ACCEPTED) {
            $this->logger?->info('ReviewerStateChange: Unapproving merge request {id}', ['id' => $mergeRequestIId]);
            $api->mergeRequests()->unapprove((int)$projectId, $mergeRequestIId);
        }
    }
}

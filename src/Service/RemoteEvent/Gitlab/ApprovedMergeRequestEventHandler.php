<?php
declare(strict_types=1);

namespace DR\Review\Service\RemoteEvent\Gitlab;

use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Model\Webhook\Gitlab\MergeRequestEvent;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\CodeReview\ChangeReviewerStateService;
use DR\Review\Service\RemoteEvent\RemoteEventHandlerInterface;
use DR\Review\Service\User\GitlabUserService;
use DR\Utils\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Throwable;

/**
 * @implements RemoteEventHandlerInterface<MergeRequestEvent>
 */
class ApprovedMergeRequestEventHandler implements RemoteEventHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly RepositoryRepository $repositoryRepository,
        private readonly CodeReviewRepository $reviewRepository,
        private readonly GitlabUserService $userService,
        private readonly ChangeReviewerStateService $changeReviewerStateService
    ) {
    }

    /**
     * @phpstan-param MergeRequestEvent $event
     * @throws Throwable
     */
    public function handle(object $event): void
    {
        Assert::isInstanceOf($event, MergeRequestEvent::class);
        if ($event->action !== 'approved') {
            return;
        }

        $repository = $this->repositoryRepository->findByProperty('gitlab-project-id', (string)$event->project->id);
        if ($repository === null) {
            $this->logger?->info('ApprovedMergeRequestEventHandler: no repository found for project id {id}', ['id' => $event->project->id]);

            return;
        }

        if ($repository->isActive() === false) {
            $this->logger?->info('ApprovedMergeRequestEventHandler: repository {name} is not active', ['name' => $repository->getName()]);

            return;
        }

        $this->logger?->info('ApprovedMergeRequestEventHandler: for repository {name}', ['name' => $repository->getName()]);

        $user = $this->userService->getUser($event->user->id, $event->user->name);
        if ($user === null) {
            $this->logger?->info('ApprovedMergeRequestEventHandler: unable to resolve user {name}', ['name' => $event->user->username]);

            return;
        }

        $this->logger?->info('ApprovedMergeRequestEventHandler: merge request {id} was approved', ['id' => $event->iid]);

        $reviews = $this->reviewRepository->findByBranchName(Assert::notNull($repository->getId()), $event->sourceBranch);
        foreach ($reviews as $review) {
            $this->changeReviewerStateService->changeState($review, $user, CodeReviewerStateType::ACCEPTED);
            $this->logger?->info(
                'ApprovedMergeRequestEventHandler: user {name} accepted review CR-{id}',
                ['name' => $event->user->username, 'id' => $review->getProjectId()]
            );
        }
    }
}

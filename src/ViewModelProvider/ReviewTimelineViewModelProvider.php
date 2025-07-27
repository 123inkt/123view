<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentAdded;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Message\Comment\CommentResolved;
use DR\Review\Message\Review\ReviewAccepted;
use DR\Review\Message\Review\ReviewOpened;
use DR\Review\Message\Review\ReviewRejected;
use DR\Review\Message\Revision\ReviewRevisionAdded;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Review\CodeReviewActivityRepository;
use DR\Review\Service\CodeReview\Activity\CodeReviewActivityFormatter;
use DR\Review\Service\CodeReview\Activity\CodeReviewActivityUrlGenerator;
use DR\Review\Service\CodeReview\Comment\ActivityCommentProvider;
use DR\Review\ViewModel\App\Review\Timeline\TimelineEntryViewModel;
use DR\Review\ViewModel\App\Review\Timeline\TimelineViewModel;
use DR\Utils\Assert;
use Symfony\Component\HttpFoundation\Request;

/**
 * @implements ProviderInterface<TimelineViewModel>
 */
readonly class ReviewTimelineViewModelProvider implements ProviderInterface
{
    private const FEED_EVENTS = [
        ReviewAccepted::NAME,
        ReviewRejected::NAME,
        ReviewOpened::NAME,
        CommentAdded::NAME,
        CommentResolved::NAME,
        CommentReplyAdded::NAME
    ];

    public function __construct(
        private RepositoryRepository $repositoryRepository,
        private CodeReviewActivityRepository $activityRepository,
        private CodeReviewActivityFormatter $activityFormatter,
        private ActivityCommentProvider $commentProvider,
        private CodeReviewActivityUrlGenerator $urlGenerator,
        private User $user
    ) {
    }

    /**
     * @param Revision[] $revisions
     */
    public function getTimelineViewModel(CodeReview $review, array $revisions): TimelineViewModel
    {
        $activities      = $this->activityRepository->findBy(['review' => $review->getId()], ['createTimestamp' => 'ASC']);
        $timelineEntries = [];

        // create TimelineEntryViewModel entries
        foreach ($activities as $activity) {
            $message = $this->activityFormatter->format($activity, $this->user);
            if ($message === null || $activity->getEventName() === CommentReplyAdded::NAME) {
                continue;
            }

            $timelineEntries[] = $entry = new TimelineEntryViewModel([$activity], $message, null);
            if ($activity->getEventName() === CommentAdded::NAME) {
                $entry->setCommentOrReply($review->getComments()->get((int)$activity->getDataValue('commentId')));
            } elseif ($activity->getEventName() === ReviewRevisionAdded::NAME) {
                $entry->setRevision($revisions[(int)$activity->getDataValue('revisionId')] ?? null);
            }
        }

        return new TimelineViewModel([], $timelineEntries);
    }

    /**
     * @TODO ANGULAR REMOVE
     *
     * @param string[] $events
     */
    public function getTimelineViewModelForFeed(User $user, array $events, ?Repository $repository = null): TimelineViewModel
    {
        $activities      = $this->activityRepository->findForUser($user->getId(), $events, $repository);
        $timelineEntries = [];

        // create TimelineEntryViewModel entries
        foreach ($activities as $activity) {
            $message = $this->activityFormatter->format($activity, $user);
            if ($message === null) {
                continue;
            }

            $url   = $this->urlGenerator->generate($activity);
            $entry = new TimelineEntryViewModel([$activity], $message, $url);
            $entry->setCommentOrReply($this->commentProvider->getCommentFor($activity));
            $timelineEntries[] = $entry;
        }

        return new TimelineViewModel([], $timelineEntries);
    }

    /**
     * @inheritDoc
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): TimelineViewModel
    {
        $repositoryId = Assert::isInstanceOf($context['request'], Request::class)->query->get('repositoryId');
        $repository   = null;
        if ($repositoryId !== null) {
            $repository = $this->repositoryRepository->find((int)Assert::numeric($repositoryId));
            Assert::notNull($repository, 'Repository not found: ' . $repositoryId);
        }

        return new TimelineViewModel($this->activityRepository->findForUser($this->user->getId(), self::FEED_EVENTS, $repository), []);
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentAdded;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Message\Revision\ReviewRevisionAdded;
use DR\Review\Repository\Review\CodeReviewActivityRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\CodeReview\Activity\CodeReviewActivityFormatter;
use DR\Review\Service\CodeReview\Activity\CodeReviewActivityUrlGenerator;
use DR\Review\ViewModel\App\Review\Timeline\TimelineEntryViewModel;
use DR\Review\ViewModel\App\Review\Timeline\TimelineViewModel;

class ReviewTimelineViewModelProvider
{
    public function __construct(
        private readonly CodeReviewActivityRepository $activityRepository,
        private readonly CodeReviewActivityFormatter $activityFormatter,
        private readonly CommentRepository $commentRepository,
        private readonly CodeReviewActivityUrlGenerator $urlGenerator,
        private readonly User $user
    ) {
    }

    public function getTimelineViewModel(CodeReview $review): TimelineViewModel
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
                $entry->setComment($review->getComments()->get((int)$activity->getDataValue('commentId')));
            } elseif ($activity->getEventName() === ReviewRevisionAdded::NAME) {
                $entry->setRevision($review->getRevisions()->get((int)$activity->getDataValue('revisionId')));
            }
        }

        return new TimelineViewModel($timelineEntries);
    }

    /**
     * @param string[] $events
     */
    public function getTimelineViewModelForFeed(User $user, array $events): TimelineViewModel
    {
        $activities      = $this->activityRepository->findForUser((int)$user->getId(), $events);
        $timelineEntries = [];

        // create TimelineEntryViewModel entries
        foreach ($activities as $activity) {
            $message = $this->activityFormatter->format($activity, $user);
            if ($message === null) {
                continue;
            }
            $entry = new TimelineEntryViewModel([$activity], $message, $this->urlGenerator->generate($activity));
            if ($activity->getEventName() === CommentAdded::NAME) {
                $entry->setComment($this->commentRepository->find((int)$activity->getDataValue('commentId')));
                if ($entry->getComment() === null) {
                    continue;
                }
            }
            $timelineEntries[] = $entry;
        }

        return new TimelineViewModel($timelineEntries);
    }
}

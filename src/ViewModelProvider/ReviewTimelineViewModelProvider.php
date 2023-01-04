<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentAdded;
use DR\Review\Repository\Review\CodeReviewActivityRepository;
use DR\Review\Service\CodeReview\CodeReviewActivityFormatter;
use DR\Review\ViewModel\App\Review\Timeline\TimelineEntryViewModel;
use DR\Review\ViewModel\App\Review\Timeline\TimelineViewModel;

class ReviewTimelineViewModelProvider
{
    public function __construct(
        private readonly CodeReviewActivityRepository $activityRepository,
        private readonly CodeReviewActivityFormatter $activityFormatter,
        private readonly User $user
    ) {
    }

    public function getTimelineViewModel(CodeReview $review): TimelineViewModel
    {
        $activities      = $this->activityRepository->findBy(['review' => $review->getId()], ['createTimestamp' => 'ASC']);
        $timelineEntries = [];

        $comments = $review->getComments();

        // create TimelineEntryViewModel entries
        foreach ($activities as $activity) {
            $message = $this->activityFormatter->format($activity, $this->user);
            if ($message === null) {
                continue;
            }
            $comment = null;
            if ($activity->getEventName() === CommentAdded::NAME) {
                $comment = $comments->get($activity->getDataValue('commentId'));
            }
            $timelineEntries[] = new TimelineEntryViewModel([$activity], $message, $comment);
        }

        return new TimelineViewModel($timelineEntries);
    }
}

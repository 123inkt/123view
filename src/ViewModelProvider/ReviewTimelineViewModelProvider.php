<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModelProvider;

use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Repository\Review\CodeReviewActivityRepository;
use DR\GitCommitNotification\Service\CodeReview\CodeReviewActivityFormatter;
use DR\GitCommitNotification\ViewModel\App\Review\Timeline\TimelineEntryViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\Timeline\TimelineViewModel;

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

        // create TimelineEntryViewModel entries
        foreach ($activities as $activity) {
            $message = $this->activityFormatter->format($this->user, $activity);
            if ($message !== null) {
                $timelineEntries[] = new TimelineEntryViewModel([$activity], $message);
            }
        }

        return new TimelineViewModel($timelineEntries);
    }
}

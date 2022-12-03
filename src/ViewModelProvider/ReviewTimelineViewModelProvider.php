<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModelProvider;

use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionAdded;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionRemoved;
use DR\GitCommitNotification\Repository\Review\CodeReviewActivityRepository;
use DR\GitCommitNotification\ViewModel\App\Review\Timeline\TimelineEntryViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\Timeline\TimelineViewModel;

class ReviewTimelineViewModelProvider
{
    private const BUNDLE_EVENTS = [ReviewRevisionAdded::NAME, ReviewRevisionRemoved::NAME];

    public function __construct(private readonly CodeReviewActivityRepository $activityRepository)
    {
    }

    public function getTimelineViewModel(CodeReview $review): TimelineViewModel
    {
        $activities = $this->activityRepository->findBy(['review' => $review->getId()], ['createTimestamp' => 'ASC']);

        $timelineEntries   = [];
        $previousEventName = null;
        $previousTimeline  = null;

        // create TimelineEntryViewModel entries
        foreach ($activities as $activity) {
            // bundle revision into the same entry
            if ($previousTimeline !== null
                && $previousEventName === $activity->getEventName()
                && in_array($activity->getEventName(), self::BUNDLE_EVENTS, true)) {
                $previousTimeline->activities[] = $activity;
                continue;
            }

            $previousEventName = $activity->getEventName();
            $previousTimeline  = $timelineEntries[] = new TimelineEntryViewModel([$activity]);
        }

        return new TimelineViewModel($timelineEntries);
    }
}

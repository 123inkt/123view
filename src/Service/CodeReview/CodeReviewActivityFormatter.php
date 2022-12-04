<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\CodeReview;

use DR\GitCommitNotification\Entity\Review\CodeReviewActivity;
use DR\GitCommitNotification\Message\Review\ReviewAccepted;
use DR\GitCommitNotification\Message\Review\ReviewClosed;
use DR\GitCommitNotification\Message\Review\ReviewCreated;
use DR\GitCommitNotification\Message\Review\ReviewOpened;
use DR\GitCommitNotification\Message\Review\ReviewRejected;
use DR\GitCommitNotification\Message\Review\ReviewResumed;
use DR\GitCommitNotification\Message\Reviewer\ReviewerAdded;
use DR\GitCommitNotification\Message\Reviewer\ReviewerRemoved;
use Symfony\Contracts\Translation\TranslatorInterface;

class CodeReviewActivityFormatter
{
    public function __construct(TranslatorInterface $translator)
    {
    }

    public function format(CodeReviewActivity $activity): string
    {
    }

    private function getTranslationId(CodeReviewActivity $activity): ?string
    {
        switch ($activity->getEventName()) {
            case ReviewerRemoved::NAME:
                return isset($activity->getData()['userId']) ? 'timeline.reviewer.removed.by' : 'timeline.reviewer.removed';
            case ReviewerAdded::NAME:
                return isset($activity->getData()['userId']) ? 'timeline.reviewer.added.by' : 'timeline.reviewer.added';
            case ReviewCreated::NAME:
                return 'timeline.review.created.from.revision';
            case ReviewClosed::NAME:
                return 'timeline.review.closed';
            case ReviewAccepted::NAME:
                return 'timeline.review.accepted';
            case ReviewRejected::NAME:
                return 'timeline.review.rejected';
            case ReviewOpened::NAME:
                return 'timeline.review.opened';
            case ReviewResumed::NAME:
                return 'timeline.review.resumed';
            default:
                return null;
        }
    }
}

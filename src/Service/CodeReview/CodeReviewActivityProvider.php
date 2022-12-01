<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\CodeReview;

use DR\GitCommitNotification\Entity\Review\CodeReviewActivity;
use DR\GitCommitNotification\Message\Comment\CommentAdded;
use DR\GitCommitNotification\Message\Review\ReviewClosed;
use DR\GitCommitNotification\Message\Review\ReviewCreated;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Repository\Review\CommentRepository;

class CodeReviewActivityProvider
{
    public function __construct(
        private readonly CodeReviewRepository $reviewRepository,
        private readonly CommentRepository $commentRepository
    ) {
    }

    public function fromReviewCreated(ReviewCreated $event): ?CodeReviewActivity
    {
        $review   = $this->reviewRepository->find($event->getReviewId());
        $revision = $review?->getRevisions()->first();
        if ($review === null || $revision === null || $revision === false) {
            return null;
        }

        $activity = new CodeReviewActivity();
        $activity->setReview($review);
        $activity->setCreateTimestamp(time());
        $activity->setData(['revisionId' => $revision->getId(), 'commit-hash' => $revision->getCommitHash()]);
        $activity->setEventName($event->getName());

        return $activity;
    }

    public function fromReviewClosed(ReviewClosed $event): ?CodeReviewActivity
    {
        $review   = $this->reviewRepository->find($event->getReviewId());
        $revision = $review?->getRevisions()->first();
        if ($review === null || $revision === null || $revision === false) {
            return null;
        }

        $activity = new CodeReviewActivity();
        $activity->setReview($review);
        $activity->setCreateTimestamp(time());
        $activity->setData(['revisionId' => $revision->getId(), 'commit-hash' => $revision->getCommitHash()]);
        $activity->setEventName($event->getName());

        return $activity;
    }

    public function fromCommendAdded(CommentAdded $event): ?CodeReviewActivity
    {
        $review  = $this->reviewRepository->find($event->getReviewId());
        $comment = $this->commentRepository->find($event->commentId);
        if ($review === null || $comment === null) {
            return null;
        }

        $activity = new CodeReviewActivity();
        $activity->setReview($review);
        $activity->setUser($comment->getUser());
        $activity->setCreateTimestamp(time());
        $activity->setData(['message' => $comment->getMessage(), 'file' => $comment->getFilePath()]);
        $activity->setEventName($event->getName());

        return $activity;
    }
}

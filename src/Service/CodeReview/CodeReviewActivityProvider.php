<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\CodeReview;

use DR\GitCommitNotification\Entity\Review\CodeReviewActivity;
use DR\GitCommitNotification\Message\Comment\CommentAdded;
use DR\GitCommitNotification\Message\Review\ReviewAccepted;
use DR\GitCommitNotification\Message\Review\ReviewClosed;
use DR\GitCommitNotification\Message\Review\ReviewCreated;
use DR\GitCommitNotification\Message\Review\ReviewOpened;
use DR\GitCommitNotification\Message\Review\ReviewRejected;
use DR\GitCommitNotification\Message\Reviewer\ReviewerAdded;
use DR\GitCommitNotification\Message\Reviewer\ReviewerRemoved;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionAdded;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionRemoved;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Repository\Review\CommentRepository;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;
use DR\GitCommitNotification\Repository\User\UserRepository;

class CodeReviewActivityProvider
{
    public function __construct(
        private readonly CodeReviewRepository $reviewRepository,
        private readonly CommentRepository $commentRepository,
        private readonly RevisionRepository $revisionRepository,
        private readonly UserRepository $userRepository
    ) {
    }

    public function fromReviewCreated(ReviewCreated $event): ?CodeReviewActivity
    {
        $review   = $this->reviewRepository->find($event->reviewId);
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

    public function fromReviewRevisionEvent(ReviewRevisionAdded|ReviewRevisionRemoved $event): ?CodeReviewActivity
    {
        $review   = $this->reviewRepository->find($event->reviewId);
        $revision = $this->revisionRepository->find($event->revisionId);
        $user     = $event->byUserId === null ? null : $this->userRepository->find($event->byUserId);
        if ($review === null || $revision === null) {
            return null;
        }

        $activity = new CodeReviewActivity();
        $activity->setReview($review);
        $activity->setUser($user);
        $activity->setCreateTimestamp(time());
        $activity->setData(['revisionId' => $revision->getId(), 'commit-hash' => $revision->getCommitHash()]);
        $activity->setEventName($event->getName());

        return $activity;
    }

    public function fromReviewEvent(ReviewAccepted|ReviewRejected|ReviewOpened|ReviewClosed $event): ?CodeReviewActivity
    {
        $review = $this->reviewRepository->find($event->getReviewId());
        $user   = $event->byUserId === null ? null : $this->userRepository->find($event->byUserId);
        if ($review === null) {
            return null;
        }

        $activity = new CodeReviewActivity();
        $activity->setReview($review);
        $activity->setUser($user);
        $activity->setCreateTimestamp(time());
        $activity->setEventName($event->getName());

        return $activity;
    }

    public function fromReviewerEvent(ReviewerAdded|ReviewerRemoved $event): ?CodeReviewActivity
    {
        $review = $this->reviewRepository->find($event->reviewId);
        $user   = $this->userRepository->find($event->byUserId);
        if ($review === null || $user === null) {
            return null;
        }

        $activity = new CodeReviewActivity();
        $activity->setReview($review);
        $activity->setUser($user);
        $activity->setCreateTimestamp(time());
        $activity->setData($event->userId !== $event->byUserId ? ['userId' => $event->userId] : []);
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

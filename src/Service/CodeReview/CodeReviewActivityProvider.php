<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\CodeReview;

use DR\GitCommitNotification\Entity\Review\CodeReviewActivity;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Message\CodeReviewAwareInterface;
use DR\GitCommitNotification\Message\Comment\CommentEventInterface;
use DR\GitCommitNotification\Message\Comment\CommentReplyEventInterface;
use DR\GitCommitNotification\Message\Review\CodeReviewEventInterface;
use DR\GitCommitNotification\Message\Review\ReviewCreated;
use DR\GitCommitNotification\Message\Reviewer\ReviewerAdded;
use DR\GitCommitNotification\Message\Reviewer\ReviewerRemoved;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionAdded;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionRemoved;
use DR\GitCommitNotification\Message\UserAwareInterface;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Repository\Review\CommentReplyRepository;
use DR\GitCommitNotification\Repository\Review\CommentRepository;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;
use DR\GitCommitNotification\Repository\User\UserRepository;

class CodeReviewActivityProvider
{
    public function __construct(
        private readonly CodeReviewRepository $reviewRepository,
        private readonly CommentRepository $commentRepository,
        private readonly CommentReplyRepository $replyRepository,
        private readonly RevisionRepository $revisionRepository,
        private readonly UserRepository $userRepository
    ) {
    }

    public function fromReviewCreated(ReviewCreated $event): ?CodeReviewActivity
    {
        $activity = $this->createActivity($event);
        $revision = $activity?->getReview()?->getRevisions()->first();
        if ($activity !== null && $revision instanceof Revision) {
            $activity->setData(['revisionId' => $revision->getId(), 'commit-hash' => $revision->getCommitHash()]);
        }

        return $activity;
    }

    public function fromReviewRevisionEvent(ReviewRevisionAdded|ReviewRevisionRemoved $event): ?CodeReviewActivity
    {
        $activity = $this->createActivity($event);
        $revision = $this->revisionRepository->find($event->revisionId);
        if ($activity === null || $revision === null) {
            return null;
        }

        $activity->setData(['revisionId' => $revision->getId(), 'commit-hash' => $revision->getCommitHash()]);

        return $activity;
    }

    public function fromReviewEvent(CodeReviewEventInterface $event): ?CodeReviewActivity
    {
        return $this->createActivity($event);
    }

    public function fromReviewerEvent(ReviewerAdded|ReviewerRemoved $event): ?CodeReviewActivity
    {
        $activity = $this->createActivity($event);
        $activity?->setData($event->userId !== $event->byUserId ? ['userId' => $event->userId] : []);

        return $activity;
    }

    public function fromCommentEvent(CommentEventInterface $event): ?CodeReviewActivity
    {
        $activity = $this->createActivity($event);
        $comment  = $this->commentRepository->find($event->getCommentId());
        if ($activity !== null && $comment !== null) {
            $activity->setUser($comment->getUser());
            $activity->setData(['message' => $comment->getMessage(), 'file' => $comment->getFilePath()]);
        }

        return $activity;
    }

    public function fromCommentReplyEvent(CommentReplyEventInterface $event): ?CodeReviewActivity
    {
        $activity = $this->createActivity($event);
        $reply    = $this->replyRepository->find($event->getCommentReplyId());
        if ($activity !== null && $reply !== null) {
            $activity->setUser($reply->getUser());
            $activity->setData(['message' => $reply->getMessage()]);
        }

        return $activity;
    }

    private function createActivity(CodeReviewAwareInterface $event): ?CodeReviewActivity
    {
        $review = $this->reviewRepository->find($event->getReviewId());
        if ($review === null) {
            return null;
        }

        $activity = new CodeReviewActivity();
        $activity->setReview($review);
        $activity->setCreateTimestamp(time());
        $activity->setEventName($event->getName());

        if ($event instanceof UserAwareInterface) {
            $activity->setUser($this->userRepository->find($event->getUserId()));
        }

        return $activity;
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Service\RemoteEvent\Gitlab\NoteEvent;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Model\Api\Gitlab\NoteEvent;
use DR\Utils\Assert;
use Symfony\Component\Clock\ClockAwareTrait;

class CommentFactory
{
    use ClockAwareTrait;

    public function create(NoteEvent $event, User $user, Revision $revision, string $filepath): Comment
    {
        $review        = Assert::notNull($revision->getReview());
        $lineReference = new LineReference(
            $event->oldPath,
            $event->newPath,
            $event->oldLine ?? $event->newLine,
            0,
            $event->newLine ?? $event->oldLine,
            $revision->getCommitHash()
        );

        $comment = new Comment();
        $comment->setFilePath($filepath);
        $comment->setTag(null);
        $comment->setLineReference($lineReference);
        $comment->setReview($review);
        $comment->setMessage($event->description);
        $comment->setUser($user);
        $comment->setExtReferenceId(sprintf('%d:%s:%d', $event->mergeRequestIId, $event->discussionId, $event->id));
        $comment->setCreateTimestamp($this->now()->getTimestamp());
        $comment->setUpdateTimestamp($this->now()->getTimestamp());

        $review->getComments()->add($comment);

        return $comment;
    }
}

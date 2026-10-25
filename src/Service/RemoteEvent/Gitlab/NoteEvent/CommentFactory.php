<?php
declare(strict_types=1);

namespace DR\Review\Service\RemoteEvent\Gitlab\NoteEvent;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentModificationEnum;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Utils\Assert;

class CommentFactory
{
    public function create(NoteEvent $event, User $user, Revision $revision, string $filepath): Comment
    {
        $review        = Assert::notNull($revision->getReview());
        $line          = Assert::notNull($event->position->oldLine ?? $event->position->newLine ?? null);
        $lineAfter     = Assert::notNull($event->position->newLine ?? $event->position->oldLine ?? null);
        $lineReference = new LineReference(
            $event->position?->oldPath,
            $event->position?->newPath,
            $line,
            0,
            $lineAfter,
            $revision->getCommitHash()
        );

        $comment = new Comment();
        $comment->setFilePath($filepath);
        $comment->setTag(null);
        $comment->setLineReference($lineReference);
        $comment->setReview($review);
        $comment->setMessage($event->note);
        $comment->setUser($user);
        $comment->setModifiedBy(CommentModificationEnum::Gitlab);
        $comment->setExtReferenceId(sprintf('%d:%s:%d', $event->mergeRequest->mergeRequestIId ?? 0, $event->discussionId, $event->id));
        $review->getComments()->add($comment);

        return $comment;
    }
}

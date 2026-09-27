<?php
declare(strict_types=1);

namespace DR\Review\Service\RemoteEvent\Gitlab\NoteEvent;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\User\User;
use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use Symfony\Component\Clock\ClockAwareTrait;

class CommentReplyFactory
{
    use ClockAwareTrait;

    public function create(NoteEvent $event, User $user, Comment $comment): CommentReply
    {
        $now = $this->now();

        $reply = new CommentReply();
        $reply->setComment($comment);
        $reply->setMessage($event->note);
        $reply->setTag(null);
        $reply->setExtReferenceId(sprintf('%d:%s:%d', $event->mergeRequest->mergeRequestIId ?? 0, $event->discussionId, $event->id));
        $reply->setUser($user);
        $reply->setCreateTimestamp($now->getTimestamp());
        $reply->setUpdateTimestamp($now->getTimestamp());
        $comment->getReplies()->add($reply);

        return $reply;
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Service\CodeReview\Comment\CommentMentionService;
use DR\Utils\Assert;

/**
 * When comment or reply is created or updated, update user mentions
 */
#[AsEntityListener(event: Events::postUpdate, method: 'commentUpdated', entity: Comment::class)]
#[AsEntityListener(event: Events::postPersist, method: 'commentUpdated', entity: Comment::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'commentReplyUpdated', entity: CommentReply::class)]
#[AsEntityListener(event: Events::postPersist, method: 'commentReplyUpdated', entity: CommentReply::class)]
class CommentEventSubscriber
{
    public function __construct(private readonly CommentMentionService $mentionService)
    {
    }

    public function commentUpdated(Comment $comment): void
    {
        $this->mentionService->updateMentions($comment);
    }

    public function commentReplyUpdated(CommentReply $reply): void
    {
        $this->mentionService->updateMentions(Assert::notNull($reply->getComment()));
    }
}

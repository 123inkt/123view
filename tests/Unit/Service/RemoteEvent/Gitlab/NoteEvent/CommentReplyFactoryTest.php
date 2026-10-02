<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\RemoteEvent\Gitlab\NoteEvent;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\User\User;
use DR\Review\Model\Api\Gitlab\MergeRequest;
use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\CommentReplyFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentReplyFactory::class)]
class CommentReplyFactoryTest extends AbstractTestCase
{
    public function testCreateCommentReply(): void
    {
        $event                                = new NoteEvent();
        $event->id                            = 42;
        $event->mergeRequest                  = new MergeRequest();
        $event->mergeRequest->mergeRequestIId = 7;
        $event->discussionId                  = 'discussion';
        $event->note                          = 'Please update this line.';

        $repository = new Repository()->setDisplayName('Repository');
        $review     = new CodeReview()->setId(123)->setRepository($repository);
        $comment    = new Comment()->setFilePath('new.php')->setReview($review);
        $user       = new User()->setId(456)->setName('User')->setEmail('user@example.com');

        $reply = new CommentReplyFactory()->create($event, $user, $comment);

        static::assertSame('Please update this line.', $reply->getMessage());
        static::assertNull($reply->getTag());
        static::assertSame('7:discussion:42', $reply->getExtReferenceId());
        static::assertSame($comment, $reply->getComment());
        static::assertSame($user, $reply->getUser());
        static::assertSame($reply, $comment->getReplies()->first());
    }
}

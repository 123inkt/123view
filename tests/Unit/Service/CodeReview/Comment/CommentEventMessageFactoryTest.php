<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview\Comment;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentReplyRemoved;
use DR\Review\Service\CodeReview\Comment\CommentEventMessageFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentEventMessageFactory::class)]
class CommentEventMessageFactoryTest extends AbstractTestCase
{
    private CommentEventMessageFactory $factory;

    public function setUp(): void
    {
        parent::setUp();
        $this->factory = new CommentEventMessageFactory();
    }

    public function testCreateAdded(): void
    {
        $user = new User();
        $user->setId(123);
        $comment = new Comment();
        $comment->setId(456);
        $comment->setFilePath('filepath');
        $comment->setMessage('message');
        $comment->setReview((new CodeReview())->setId(789));

        $event = $this->factory->createAdded($comment, $user);
        static::assertSame(456, $event->getCommentId());
        static::assertSame(123, $event->getUserId());
        static::assertSame('filepath', $event->file);
        static::assertSame('message', $event->message);
        static::assertSame('comment-added', $event->getName());
    }

    public function testCreateUpdated(): void
    {
        $user = new User();
        $user->setId(123);
        $comment = new Comment();
        $comment->setId(456);
        $comment->setFilePath('filepath');
        $comment->setMessage('message');
        $comment->setReview((new CodeReview())->setId(789));

        $event = $this->factory->createUpdated($comment, $user, 'original');
        static::assertSame(456, $event->getCommentId());
        static::assertSame(123, $event->getUserId());
        static::assertSame('filepath', $event->file);
        static::assertSame('message', $event->message);
        static::assertSame('comment-updated', $event->getName());
    }

    public function testCreateResolved(): void
    {
        $user = new User();
        $user->setId(123);
        $comment = new Comment();
        $comment->setId(456);
        $comment->setFilePath('filepath');
        $comment->setMessage('message');
        $comment->setReview((new CodeReview())->setId(789));

        $event = $this->factory->createResolved($comment, $user);
        static::assertSame(456, $event->getCommentId());
        static::assertSame(123, $event->getUserId());
        static::assertSame('filepath', $event->file);
        static::assertSame('comment-resolved', $event->getName());
    }

    public function testCreateUnresolved(): void
    {
        $user = new User();
        $user->setId(123);
        $comment = new Comment();
        $comment->setId(456);
        $comment->setFilePath('filepath');
        $comment->setMessage('message');
        $comment->setReview((new CodeReview())->setId(789));

        $event = $this->factory->createUnresolved($comment, $user);
        static::assertSame(456, $event->getCommentId());
        static::assertSame(123, $event->getUserId());
        static::assertSame('filepath', $event->file);
        static::assertSame('comment-unresolved', $event->getName());
    }

    public function testCreateRemoved(): void
    {
        $user = new User();
        $user->setId(123);
        $comment = new Comment();
        $comment->setId(456);
        $comment->setFilePath('filepath');
        $comment->setMessage('message');
        $comment->setReview((new CodeReview())->setId(789));

        $event = $this->factory->createRemoved($comment, $user);
        static::assertSame(456, $event->getCommentId());
        static::assertSame(123, $event->getUserId());
        static::assertSame('filepath', $event->file);
        static::assertSame('message', $event->message);
        static::assertSame('comment-removed', $event->getName());
    }

    public function testCreateReplyRemoved(): void
    {
        $ownerUser = (new User())->setId(111);
        $user      = (new User())->setId(222);

        $comment = new Comment();
        $comment->setId(333);
        $comment->setReview((new CodeReview())->setId(444));

        $reply = new CommentReply();
        $reply->setId(555);
        $reply->setMessage('message');
        $reply->setExtReferenceId('external-reference-id');
        $reply->setComment($comment);
        $reply->setUser($ownerUser);

        $expected = new CommentReplyRemoved(444, 333, 555, 111, 222, 'message', 'external-reference-id');
        $event    = $this->factory->createReplyRemoved($reply, $user);
        static::assertEquals($expected, $event);
    }
}

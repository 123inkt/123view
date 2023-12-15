<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview\Comment;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\User\User;
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
        $comment->setReview(new CodeReview());

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
        $comment->setReview(new CodeReview());

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
        $comment->setReview(new CodeReview());

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
        $comment->setReview(new CodeReview());

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
        $comment->setReview(new CodeReview());

        $event = $this->factory->createRemoved($comment, $user);
        static::assertSame(456, $event->getCommentId());
        static::assertSame(123, $event->getUserId());
        static::assertSame('filepath', $event->file);
        static::assertSame('message', $event->message);
        static::assertSame('comment-removed', $event->getName());
    }
}

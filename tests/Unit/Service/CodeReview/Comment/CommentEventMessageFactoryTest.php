<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview\Comment;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\User\User;
use DR\Review\Service\CodeReview\Comment\CommentEventMessageFactory;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Service\CodeReview\Comment\CommentEventMessageFactory
 */
class CommentEventMessageFactoryTest extends AbstractTestCase
{
    private CommentEventMessageFactory $factory;

    public function setUp(): void
    {
        parent::setUp();
        $this->factory = new CommentEventMessageFactory();
    }

    /**
     * @covers ::createAdded
     */
    public function testCreateAdded(): void
    {
        $user = new User();
        $user->setId(123);
        $comment = new Comment();
        $comment->setId(456);
        $comment->setLineReference(new LineReference('filepath'));
        $comment->setMessage('message');

        $event = $this->factory->createAdded($comment, $user);
        static::assertSame(456, $event->getCommentId());
        static::assertSame(123, $event->getUserId());
        static::assertSame('filepath', $event->file);
        static::assertSame('message', $event->message);
        static::assertSame('comment-added', $event->getName());
    }

    /**
     * @covers ::createUpdated
     */
    public function testCreateUpdated(): void
    {
        $user = new User();
        $user->setId(123);
        $comment = new Comment();
        $comment->setId(456);
        $comment->setLineReference(new LineReference('filepath'));
        $comment->setMessage('message');

        $event = $this->factory->createUpdated($comment, $user, 'original');
        static::assertSame(456, $event->getCommentId());
        static::assertSame(123, $event->getUserId());
        static::assertSame('filepath', $event->file);
        static::assertSame('message', $event->message);
        static::assertSame('comment-updated', $event->getName());
    }

    /**
     * @covers ::createResolved
     */
    public function testCreateResolved(): void
    {
        $user = new User();
        $user->setId(123);
        $comment = new Comment();
        $comment->setId(456);
        $comment->setLineReference(new LineReference('filepath'));
        $comment->setMessage('message');

        $event = $this->factory->createResolved($comment, $user);
        static::assertSame(456, $event->getCommentId());
        static::assertSame(123, $event->getUserId());
        static::assertSame('filepath', $event->file);
        static::assertSame('comment-resolved', $event->getName());
    }

    /**
     * @covers ::createUnresolved
     */
    public function testCreateUnresolved(): void
    {
        $user = new User();
        $user->setId(123);
        $comment = new Comment();
        $comment->setId(456);
        $comment->setLineReference(new LineReference('filepath'));
        $comment->setMessage('message');

        $event = $this->factory->createUnresolved($comment, $user);
        static::assertSame(456, $event->getCommentId());
        static::assertSame(123, $event->getUserId());
        static::assertSame('filepath', $event->file);
        static::assertSame('comment-unresolved', $event->getName());
    }

    /**
     * @covers ::createRemoved
     */
    public function testCreateRemoved(): void
    {
        $user = new User();
        $user->setId(123);
        $comment = new Comment();
        $comment->setId(456);
        $comment->setLineReference(new LineReference('filepath'));
        $comment->setMessage('message');

        $event = $this->factory->createRemoved($comment, $user);
        static::assertSame(456, $event->getCommentId());
        static::assertSame(123, $event->getUserId());
        static::assertSame('filepath', $event->file);
        static::assertSame('message', $event->message);
        static::assertSame('comment-removed', $event->getName());
    }
}

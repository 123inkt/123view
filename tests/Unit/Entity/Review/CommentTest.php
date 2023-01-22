<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Review;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use Doctrine\Common\Collections\ArrayCollection;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\Review\NotificationStatus;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Entity\Review\Comment
 * @covers ::__construct
 */
class CommentTest extends AbstractTestCase
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        $config = (new ConstraintConfig())->setExcludedMethods(['setReplies', 'setLineReference', 'setMentions']);
        static::assertAccessorPairs(Comment::class, $config);
    }

    /**
     * @covers ::setNotificationStatus
     * @covers ::getNotificationStatus
     */
    public function testNotificationStatus(): void
    {
        $comment = new Comment();

        $statusA = $comment->getNotificationStatus();
        $statusB = $comment->getNotificationStatus();
        static::assertSame($statusA, $statusB);

        $statusC = new NotificationStatus();
        $comment->setNotificationStatus($statusC);
        static::assertSame($statusC, $comment->getNotificationStatus());
    }

    /**
     * @covers ::setLineReference
     * @covers ::getLineReference
     */
    public function testLineReference(): void
    {
        $comment = new Comment();
        static::assertNull($comment->getLineReference());

        $comment->setLineReference(new LineReference('foobar', 1, 2, 3));
        static::assertEquals(new LineReference('foobar', 1, 2, 3), $comment->getLineReference());
    }

    /**
     * @covers ::getReplies
     * @covers ::setReplies
     */
    public function testReplies(): void
    {
        $collection = new ArrayCollection();

        $comment = new Comment();
        static::assertInstanceOf(ArrayCollection::class, $comment->getReplies());

        $comment->setReplies($collection);
        static::assertSame($collection, $comment->getReplies());
    }

    /**
     * @covers ::getMentions
     * @covers ::setMentions
     */
    public function testMentions(): void
    {
        $collection = new ArrayCollection();

        $comment = new Comment();
        static::assertInstanceOf(ArrayCollection::class, $comment->getMentions());

        $comment->setMentions($collection);
        static::assertSame($collection, $comment->getMentions());
    }
}

<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Review;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use Doctrine\Common\Collections\ArrayCollection;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Entity\Review\LineReference;
use DR\GitCommitNotification\Entity\Review\NotificationStatus;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Review\Comment
 * @covers ::__construct
 */
class CommentTest extends AbstractTestCase
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        $config = (new ConstraintConfig())->setExcludedMethods(['setReplies', 'setLineReference']);
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
}

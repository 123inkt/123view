<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Review;

use DR\GitCommitNotification\Entity\Review\CommentReply;
use DR\GitCommitNotification\Entity\Review\NotificationStatus;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Review\CommentReply
 */
class CommentReplyTest extends AbstractTestCase
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(CommentReply::class);
    }

    /**
     * @covers ::setNotificationStatus
     * @covers ::getNotificationStatus
     */
    public function testNotificationStatus(): void
    {
        $comment = new CommentReply();

        $statusA = $comment->getNotificationStatus();
        $statusB = $comment->getNotificationStatus();
        static::assertSame($statusA, $statusB);

        $statusC = new NotificationStatus();
        $comment->setNotificationStatus($statusC);
        static::assertSame($statusC, $comment->getNotificationStatus());
    }
}

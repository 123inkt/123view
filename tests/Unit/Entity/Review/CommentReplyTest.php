<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Review;

use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\Review\NotificationStatus;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Entity\Review\CommentReply
 */
class CommentReplyTest extends AbstractTestCase
{
    /**
     * @covers ::setId
     * @covers ::getId
     * @covers ::getMessage
     * @covers ::setMessage
     * @covers ::getCreateTimestamp
     * @covers ::setCreateTimestamp
     * @covers ::getUpdateTimestamp
     * @covers ::setUpdateTimestamp
     * @covers ::getNotificationStatus
     * @covers ::setNotificationStatus
     * @covers ::getComment
     * @covers ::setComment
     * @covers ::getUser
     * @covers ::setUser
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

<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Review;

use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\Review\NotificationStatus;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentReply::class)]
class CommentReplyTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(CommentReply::class);
    }

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

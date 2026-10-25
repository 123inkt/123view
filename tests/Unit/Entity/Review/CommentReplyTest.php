<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Review;

use DateTimeImmutable;
use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use DR\Review\Entity\Review\CommentModificationEnum;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\Review\NotificationStatus;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentReply::class)]
class CommentReplyTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        $config = new ConstraintConfig()->setExcludedMethods(['setCreateTimestamp', 'setUpdateTimestamp']);
        static::assertAccessorPairs(CommentReply::class, $config);
    }

    public function testTimestampAccessors(): void
    {
        $timestamp = new DateTimeImmutable('@123');
        $reply     = new CommentReply();

        $reply->setCreateTimestamp($timestamp);
        $reply->setUpdateTimestamp($timestamp);
        static::assertSame(123, $reply->getCreateTimestamp()->getTimestamp());
        static::assertSame(123, $reply->getUpdateTimestamp()->getTimestamp());
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

    public function testModifiedBy(): void
    {
        $reply = new CommentReply();

        static::assertSame(CommentModificationEnum::Local, $reply->getModifiedBy());
        $reply->setModifiedBy(CommentModificationEnum::Gitlab);
        static::assertSame(CommentModificationEnum::Gitlab, $reply->getModifiedBy());
    }
}

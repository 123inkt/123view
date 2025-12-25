<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Review;

use DR\Review\Entity\Review\NotificationStatus;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NotificationStatus::class)]
class NotificationStatusTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        $status = new NotificationStatus(0);
        static::assertFalse($status->hasStatus(NotificationStatus::STATUS_CREATED));
        static::assertFalse($status->hasStatus(NotificationStatus::STATUS_UPDATED));
        static::assertFalse($status->hasStatus(NotificationStatus::STATUS_RESOLVED));

        $status->addStatus(NotificationStatus::STATUS_CREATED);
        static::assertTrue($status->hasStatus(NotificationStatus::STATUS_CREATED));
        static::assertFalse($status->hasStatus(NotificationStatus::STATUS_UPDATED));
        static::assertFalse($status->hasStatus(NotificationStatus::STATUS_RESOLVED));

        $status->addStatus(NotificationStatus::STATUS_UPDATED);
        static::assertTrue($status->hasStatus(NotificationStatus::STATUS_CREATED));
        static::assertTrue($status->hasStatus(NotificationStatus::STATUS_UPDATED));
        static::assertFalse($status->hasStatus(NotificationStatus::STATUS_RESOLVED));

        $status->removeStatus(NotificationStatus::STATUS_UPDATED);
        static::assertTrue($status->hasStatus(NotificationStatus::STATUS_CREATED));
        static::assertFalse($status->hasStatus(NotificationStatus::STATUS_UPDATED));
        static::assertFalse($status->hasStatus(NotificationStatus::STATUS_RESOLVED));

        static::assertSame(1, $status->getStatus());
    }

    public function testAll(): void
    {
        $status = NotificationStatus::all();
        static::assertTrue($status->hasStatus(NotificationStatus::STATUS_CREATED));
        static::assertTrue($status->hasStatus(NotificationStatus::STATUS_UPDATED));
        static::assertTrue($status->hasStatus(NotificationStatus::STATUS_RESOLVED));
    }
}

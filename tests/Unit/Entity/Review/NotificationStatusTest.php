<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Review;

use DR\Review\Entity\Review\NotificationStatus;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Entity\Review\NotificationStatus
 * @covers ::__construct
 */
class NotificationStatusTest extends AbstractTestCase
{
    /**
     * @covers ::hasStatus
     * @covers ::addStatus
     * @covers ::removeStatus
     * @covers ::getStatus
     */
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
}

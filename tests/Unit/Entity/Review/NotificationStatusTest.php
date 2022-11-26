<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Review;

use DR\GitCommitNotification\Entity\Review\NotificationStatus;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Review\NotificationStatus
 * @covers ::__construct
 */
class NotificationStatusTest extends AbstractTestCase
{
    /**
     * @covers ::<public>
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

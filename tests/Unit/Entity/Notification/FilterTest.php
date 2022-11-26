<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Notification;

use DR\GitCommitNotification\Entity\Notification\Filter;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Notification\Filter
 */
class FilterTest extends AbstractTestCase
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        static::assertNull((new Filter())->getId());
        static::assertAccessorPairs(Filter::class);
    }
}

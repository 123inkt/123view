<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Notification;

use DR\Review\Entity\Notification\Filter;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Entity\Notification\Filter
 */
class FilterTest extends AbstractTestCase
{
    /**
     * @covers ::setPattern
     * @covers ::getPattern
     * @covers ::setRule
     * @covers ::getRule
     * @covers ::setType
     * @covers ::getType
     * @covers ::setInclusion
     * @covers ::getInclusion
     */
    public function testAccessorPairs(): void
    {
        static::assertNull((new Filter())->getId());
        static::assertAccessorPairs(Filter::class);
    }
}

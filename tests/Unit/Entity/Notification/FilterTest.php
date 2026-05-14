<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Notification;

use DR\Review\Entity\Notification\Filter;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Filter::class)]
class FilterTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        static::assertFalse((new Filter())->hasId());
        static::assertAccessorPairs(Filter::class);
    }

    public function testClone(): void
    {
        clone new Filter();
        static::expectNotToPerformAssertions();
    }
}

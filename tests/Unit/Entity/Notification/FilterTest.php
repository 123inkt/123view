<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Notification;

use DR\Review\Entity\Notification\Filter;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionProperty;

#[CoversClass(Filter::class)]
class FilterTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(Filter::class);
    }

    public function testId(): void
    {
        $filter = new Filter();
        static::assertFalse($filter->hasId());

        new ReflectionProperty(Filter::class, 'id')->setValue($filter, 123);
        static::assertTrue($filter->hasId());
        static::assertSame(123, $filter->getId());
    }

    public function testClone(): void
    {
        clone new Filter();
        static::expectNotToPerformAssertions();
    }
}

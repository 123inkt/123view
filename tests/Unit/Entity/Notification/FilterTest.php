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
        static::assertNull((new Filter())->getId());
        static::assertAccessorPairs(Filter::class);
    }
}

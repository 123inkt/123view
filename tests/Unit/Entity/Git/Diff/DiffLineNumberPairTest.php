<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffLineNumberPair;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DiffLineNumberPair::class)]
class DiffLineNumberPairTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(DiffLineNumberPair::class);
    }

    public function testIncrement(): void
    {
        $pair = new DiffLineNumberPair(50, 100);

        $pair->increment(true);
        static::assertSame(50, $pair->getLineNumberBefore());
        static::assertSame(101, $pair->getLineNumberAfter());

        $pair->increment(false);
        static::assertSame(51, $pair->getLineNumberBefore());
        static::assertSame(101, $pair->getLineNumberAfter());

        $pair->increment(false, 10);
        static::assertSame(61, $pair->getLineNumberBefore());
        static::assertSame(101, $pair->getLineNumberAfter());
    }
}

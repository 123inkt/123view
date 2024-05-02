<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Git\Diff;

use DR\JBDiff\ComparisonPolicy;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DiffComparePolicy::class)]
class DiffComparePolicyTest extends AbstractTestCase
{
    public function testValues(): void
    {
        static::assertSame(['all', 'trim', 'ignore', 'ignore_empty_lines'], DiffComparePolicy::values());
    }

    public function testToComparisonPolicy(): void
    {
        static::assertSame(ComparisonPolicy::DEFAULT, DiffComparePolicy::ALL->toComparisonPolicy());
        static::assertSame(ComparisonPolicy::TRIM_WHITESPACES, DiffComparePolicy::TRIM->toComparisonPolicy());
        static::assertSame(ComparisonPolicy::IGNORE_WHITESPACES, DiffComparePolicy::IGNORE->toComparisonPolicy());
        static::assertSame(ComparisonPolicy::IGNORE_WHITESPACES, DiffComparePolicy::IGNORE_EMPTY_LINES->toComparisonPolicy());
    }
}

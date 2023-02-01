<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Diff\Bundle;

use DR\Review\Service\Git\Diff\Bundle\DiffLineCompareResult;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Service\Git\Diff\Bundle\DiffLineCompareResult
 * @covers ::__construct
 */
class DiffLineCompareResultTest extends AbstractTestCase
{
    /**
     * @covers ::isAdditionsOnly
     */
    public function testIsAdditionsOnly(): void
    {
        $result = new DiffLineCompareResult(0, 5, 0, 0);
        static::assertTrue($result->isAdditionsOnly());

        $result = new DiffLineCompareResult(5, 5, 0, 0);
        static::assertFalse($result->isAdditionsOnly());

        $result = new DiffLineCompareResult(0, 5, 5, 0);
        static::assertTrue($result->isAdditionsOnly());
    }

    /**
     * @covers ::isRemovalOnly
     */
    public function testIsRemovalOnly(): void
    {
        $result = new DiffLineCompareResult(5, 0, 0, 0);
        static::assertTrue($result->isRemovalOnly());

        $result = new DiffLineCompareResult(5, 5, 0, 0);
        static::assertFalse($result->isRemovalOnly());

        $result = new DiffLineCompareResult(5, 0, 5, 0);
        static::assertTrue($result->isRemovalOnly());
    }

    /**
     * @covers ::isWhitespaceOnly
     */
    public function testIsWhitespaceOnly(): void
    {
        $result = new DiffLineCompareResult(0, 0, 5, 0);
        static::assertTrue($result->isWhitespaceOnly());

        $result = new DiffLineCompareResult(0, 0, 0, 0);
        static::assertFalse($result->isWhitespaceOnly());

        $result = new DiffLineCompareResult(0, 5, 5, 0);
        static::assertFalse($result->isWhitespaceOnly());

        $result = new DiffLineCompareResult(5, 0, 5, 0);
        static::assertFalse($result->isWhitespaceOnly());
    }
}

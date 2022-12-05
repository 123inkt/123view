<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffBlock;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Entity\Git\Diff\DiffBlock
 */
class DiffBlockTest extends AbstractTestCase
{
    /**
     * @covers ::isLineVisible
     */
    public function testIsLineVisible(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineB = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineC = new DiffLine(DiffLine::STATE_ADDED, []);

        $block        = new DiffBlock();
        $block->lines = [$lineA, $lineB, $lineC];

        static::assertFalse($block->isLineVisible(0, 1));
        static::assertTrue($block->isLineVisible(1, 1));
        static::assertTrue($block->isLineVisible(2, 1));
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffBlock;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Service\Git\Diff\DiffFileUpdater;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Service\Git\Diff\DiffFileUpdater
 */
class DiffFileUpdaterTest extends AbstractTestCase
{
    private DiffFileUpdater $updater;

    protected function setUp(): void
    {
        parent::setUp();
        $this->updater = new DiffFileUpdater();
    }

    /**
     * @covers ::update
     */
    public function testUpdateVisibleLines(): void
    {
        $lineA        = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineB        = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineC        = new DiffLine(DiffLine::STATE_ADDED, []);
        $lineD        = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineE        = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $block        = new DiffBlock();
        $block->lines = [$lineA, $lineB, $lineC, $lineD, $lineE];
        $file         = new DiffFile();
        $file->addBlock($block);

        $this->updater->update([$file], 1, 1000);
        static::assertFalse($lineA->visible);
        static::assertTrue($lineB->visible);
        static::assertTrue($lineC->visible);
        static::assertTrue($lineD->visible);
        static::assertFalse($lineE->visible);
    }

    /**
     * @covers ::update
     */
    public function testUpdateVisibleLinesWithMultipleChanges(): void
    {
        $lineA        = new DiffLine(DiffLine::STATE_ADDED, []);
        $lineB        = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineC        = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineD        = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineE        = new DiffLine(DiffLine::STATE_ADDED, []);
        $block        = new DiffBlock();
        $block->lines = [$lineA, $lineB, $lineC, $lineD, $lineE];
        $file         = new DiffFile();
        $file->addBlock($block);

        $this->updater->update([$file], 1, 1000);
        static::assertTrue($lineA->visible);
        static::assertTrue($lineB->visible);
        static::assertFalse($lineC->visible);
        static::assertTrue($lineD->visible);
        static::assertTrue($lineE->visible);
    }

    /**
     * @covers ::update
     */
    public function testUpdateRemoveInvisibleLines(): void
    {
        $lineA        = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineB        = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineC        = new DiffLine(DiffLine::STATE_ADDED, []);
        $lineD        = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineE        = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $block        = new DiffBlock();
        $block->lines = [$lineA, $lineB, $lineC, $lineD, $lineE];
        $file         = new DiffFile();
        $file->addBlock($block);

        $this->updater->update([$file], 1, 0);
        static::assertSame([$lineB, $lineC, $lineD], $file->getBlocks()[0]->lines);
    }
}

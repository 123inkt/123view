<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModel\App\Review;

use DigitalRevolution\AccessorPairConstraint\AccessorPairAsserter;
use DR\GitCommitNotification\Entity\Git\Diff\DiffBlock;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Git\Diff\DiffLine;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\ViewModel\App\Review\FileDiffViewModel;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ViewModel\App\Review\FileDiffViewModel
 * @covers ::__construct
 */
class FileDiffViewModelTest extends AbstractTestCase
{
    use AccessorPairAsserter;

    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(FileDiffViewModel::class);
    }

    /**
     * @covers ::getMaxLineNumberLength
     */
    public function testGetMaxLineNumberLength(): void
    {
        $lineA                   = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineA->lineNumberBefore = 100;
        $lineA->lineNumberAfter  = 200;

        $lineB                   = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineB->lineNumberBefore = 1000;
        $lineB->lineNumberAfter  = null;

        $lineC                   = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineC->lineNumberBefore = null;
        $lineC->lineNumberAfter  = 20000;

        $block        = new DiffBlock();
        $block->lines = [$lineA, $lineB, $lineC];

        $file = new DiffFile();
        $file->addBlock($block);

        $model = new FileDiffViewModel(null);
        static::assertSame(4, $model->getMaxLineNumberLength($file, true));
        static::assertSame(5, $model->getMaxLineNumberLength($file, false));
    }
}

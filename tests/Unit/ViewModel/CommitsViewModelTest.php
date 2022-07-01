<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModel;

use DigitalRevolution\AccessorPairConstraint\AccessorPairAsserter;
use DR\GitCommitNotification\Entity\Git\Diff\DiffBlock;
use DR\GitCommitNotification\Entity\Git\Diff\DiffLine;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\ViewModel\CommitsViewModel;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ViewModel\CommitsViewModel
 * @covers ::__construct
 */
class CommitsViewModelTest extends AbstractTestCase
{
    use AccessorPairAsserter;

    /**
     * @covers ::getTheme
     * @covers ::getCommits
     * @covers ::getExternalLinks
     */
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(CommitsViewModel::class);
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

        $model = new CommitsViewModel([], 'foobar', []);
        static::assertSame(4, $model->getMaxLineNumberLength($block, true));
        static::assertSame(5, $model->getMaxLineNumberLength($block, false));
    }
}

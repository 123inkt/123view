<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffBlock;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Service\Git\Diff\UnifiedDiffSplitter;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Service\Git\Diff\UnifiedDiffSplitter
 */
class UnifiedDiffSplitterTest extends AbstractTestCase
{
    private UnifiedDiffSplitter $splitter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->splitter = new UnifiedDiffSplitter();
    }

    /**
     * @covers ::splitFile
     * @covers ::balanceLines
     */
    public function testSplitFile(): void
    {
        $diffLineA = new DiffLine(DiffLine::STATE_ADDED, []);
        $diffLineB = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $diffLineC = new DiffLine(DiffLine::STATE_REMOVED, []);
        $diffLineD = new DiffLine(DiffLine::STATE_UNCHANGED, []);

        $block        = new DiffBlock();
        $block->lines = [$diffLineA, $diffLineB, $diffLineC, $diffLineD];
        $rightFile    = new DiffFile();
        $rightFile->addBlock($block);

        $leftFile = $this->splitter->splitFile($rightFile);

        $leftBlocks = $leftFile->getBlocks();
        static::assertCount(1, $leftBlocks);
        self::assertLines([DiffLine::STATE_EMPTY, DiffLine::STATE_UNCHANGED, DiffLine::STATE_REMOVED, DiffLine::STATE_UNCHANGED], $leftBlocks[0]);

        $rightBlocks = $rightFile->getBlocks();
        static::assertCount(1, $rightBlocks);
        self::assertLines([DiffLine::STATE_ADDED, DiffLine::STATE_UNCHANGED, DiffLine::STATE_EMPTY, DiffLine::STATE_UNCHANGED], $rightBlocks[0]);
    }

    /**
     * @param int[] $expectedStates
     */
    private static function assertLines(array $expectedStates, DiffBlock $block): void
    {
        foreach ($block->lines as $index => $line) {
            static::assertSame($expectedStates[$index], $line->state);
        }
    }
}

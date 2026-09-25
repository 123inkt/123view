<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffBlock;
use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Service\Git\Diff\Optimizer\DiffLineChangeSetOptimizer;
use DR\Review\Service\Git\Diff\UnifiedDiffEmphasizer;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(UnifiedDiffEmphasizer::class)]
class UnifiedDiffEmphasizerTest extends AbstractTestCase
{
    private DiffLineChangeSetOptimizer&MockObject $optimizer;
    private UnifiedDiffEmphasizer                 $emphasizer;

    public function setUp(): void
    {
        parent::setUp();
        $this->optimizer  = $this->createMock(DiffLineChangeSetOptimizer::class);
        $this->emphasizer = new UnifiedDiffEmphasizer($this->optimizer);
    }

    public function testEmphasizeFile(): void
    {
        $line1 = new DiffLine(DiffLine::STATE_UNCHANGED, [new DiffChange(DiffChange::UNCHANGED, 'line1')]);
        $line2 = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'line2')]);
        $line3 = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'line3')]);
        $line4 = new DiffLine(DiffLine::STATE_UNCHANGED, [new DiffChange(DiffChange::UNCHANGED, 'line4')]);

        $block        = new DiffBlock();
        $block->lines = [$line1, $line2, $line3, $line4];
        $file         = new DiffFile();
        $file->addBlock($block);

        $this->optimizer->expects($this->once())->method('optimize');

        $this->emphasizer->emphasizeFile($file, DiffComparePolicy::IGNORE);
        static::assertEquals([$line1, $line2, $line3, $line4], $block->lines);
    }
}

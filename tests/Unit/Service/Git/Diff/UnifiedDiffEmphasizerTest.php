<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffBlock;
use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Git\Diff\DiffLineDiffer;
use DR\Review\Service\Git\Diff\UnifiedDiffEmphasizer;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Service\Git\Diff\UnifiedDiffEmphasizer
 * @covers ::__construct
 */
class UnifiedDiffEmphasizerTest extends AbstractTestCase
{
    private DiffLineDiffer&MockObject $differ;
    private UnifiedDiffEmphasizer     $emphasizer;

    public function setUp(): void
    {
        parent::setUp();
        $this->differ     = $this->createMock(DiffLineDiffer::class);
        $this->emphasizer = new UnifiedDiffEmphasizer($this->differ);
    }

    /**
     * @covers ::emphasizeFile
     */
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

        $this->differ->expects(self::once())->method('diff')->with($line2, $line3);

        $this->emphasizer->emphasizeFile($file);
    }

    /**
     * @covers ::emphasizeLines
     */
    public function testEmphasizeLines(): void
    {
        $line1 = new DiffLine(DiffLine::STATE_UNCHANGED, [new DiffChange(DiffChange::UNCHANGED, 'line1')]);
        $line2 = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'line2')]);
        $line3 = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'line3')]);
        $line4 = new DiffLine(DiffLine::STATE_UNCHANGED, [new DiffChange(DiffChange::UNCHANGED, 'line4')]);

        $this->differ->expects(self::once())->method('diff')->with($line2, $line3);

        $lines = $this->emphasizer->emphasizeLines([$line1, $line2, $line3, $line4]);
        static::assertSame([$line1, $line2, $line3, $line4], $lines);
    }
}

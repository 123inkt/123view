<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffBlock;
use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Service\Git\Diff\UnifiedDiffPruner;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UnifiedDiffPruner::class)]
class UnifiedDiffPrunerTest extends AbstractTestCase
{
    private UnifiedDiffPruner $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UnifiedDiffPruner();
    }

    public function testPruneEmptyLines(): void
    {
        $line  = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, '')]);
        $block = new DiffBlock();
        $file  = new DiffFile();

        $block->lines[] = $line;
        $file->addBlock($block);

        $this->service->pruneEmptyLines($file);
        static::assertSame(DiffLine::STATE_UNCHANGED, $line->state);
    }

    public function testPruneEmptyLinesShouldSkipNonEmptyCodeLines(): void
    {
        $line  = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'code')]);
        $block = new DiffBlock();
        $file  = new DiffFile();

        $block->lines[] = $line;
        $file->addBlock($block);

        $this->service->pruneEmptyLines($file);
        static::assertSame(DiffLine::STATE_ADDED, $line->state);
    }

    public function testPruneEmptyLinesShouldSkipChanges(): void
    {
        $line  = new DiffLine(DiffLine::STATE_CHANGED, [new DiffChange(DiffChange::ADDED, '')]);
        $block = new DiffBlock();
        $file  = new DiffFile();

        $block->lines[] = $line;
        $file->addBlock($block);

        $this->service->pruneEmptyLines($file);
        static::assertSame(DiffLine::STATE_CHANGED, $line->state);
    }

    public function testPruneEmptyLinesShouldSkiMultiChange(): void
    {
        $line  = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, ''), new DiffChange(DiffChange::ADDED, '')]);
        $block = new DiffBlock();
        $file  = new DiffFile();

        $block->lines[] = $line;
        $file->addBlock($block);

        $this->service->pruneEmptyLines($file);
        static::assertSame(DiffLine::STATE_ADDED, $line->state);
    }

    public function testPruneLinesMultiLines(): void
    {
        $lineA  = new DiffLine(DiffLine::STATE_UNCHANGED, [new DiffChange(DiffChange::UNCHANGED, 'unchanged')]);
        $lineB  = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, '')]);
        $lineC  = new DiffLine(DiffLine::STATE_UNCHANGED, [new DiffChange(DiffChange::UNCHANGED, 'unchanged')]);
        $block = new DiffBlock();
        $file  = new DiffFile();

        $block->lines = [$lineA, $lineB, $lineC];
        $file->addBlock($block);

        $this->service->pruneEmptyLines($file);
        static::assertSame(DiffLine::STATE_UNCHANGED, $lineB->state);
    }

    public function testPruneLinesMultiLinesModified(): void
    {
        $lineA  = new DiffLine(DiffLine::STATE_UNCHANGED, [new DiffChange(DiffChange::UNCHANGED, 'unchanged')]);
        $lineB  = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'code')]);
        $lineC  = new DiffLine(DiffLine::STATE_UNCHANGED, [new DiffChange(DiffChange::UNCHANGED, 'unchanged')]);
        $block = new DiffBlock();
        $file  = new DiffFile();

        $block->lines = [$lineA, $lineB, $lineC];
        $file->addBlock($block);

        $this->service->pruneEmptyLines($file);
        static::assertSame(DiffLine::STATE_ADDED, $lineB->state);
    }
}

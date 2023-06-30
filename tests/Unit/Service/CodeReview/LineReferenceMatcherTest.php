<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview;

use DR\Review\Entity\Git\Diff\DiffBlock;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Service\CodeReview\LineReferenceMatcher;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LineReferenceMatcher::class)]
class LineReferenceMatcherTest extends AbstractTestCase
{
    private LineReferenceMatcher $matcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->matcher = new LineReferenceMatcher();
    }

    public function testExactMatchAddedLine(): void
    {
        $line                  = new DiffLine(DiffLine::STATE_ADDED, []);
        $line->lineNumberAfter = 123;

        $reference = new LineReference('/file/path', 10, 20, 123);

        static::assertSame($line, $this->matcher->exactMatch([$line], $reference));
    }

    public function testExactMatchRemovedLine(): void
    {
        $line                   = new DiffLine(DiffLine::STATE_CHANGED, []);
        $line->lineNumberBefore = 123;

        $reference = new LineReference('/file/path', 123, 0, 0);

        static::assertSame($line, $this->matcher->exactMatch([$line], $reference));
    }

    public function testExactMatchModifiedLine(): void
    {
        $line                   = new DiffLine(DiffLine::STATE_CHANGED, []);
        $line->lineNumberBefore = 123;
        $line->lineNumberAfter  = 456;

        $reference = new LineReference('/file/path', 123, 0, 456);

        static::assertSame($line, $this->matcher->exactMatch([$line], $reference));
    }

    public function testExactMatchNoMatch(): void
    {
        $line                   = new DiffLine(DiffLine::STATE_CHANGED, []);
        $line->lineNumberBefore = 123;
        $line->lineNumberAfter  = 456;

        $reference = new LineReference('/file/path', 123, 456, 789);

        static::assertNull($this->matcher->exactMatch([$line], $reference));
    }

    public function testBestEffortMatch(): void
    {
    }

    /**
     * @covers ::findLinesAround
     */
    public function testFindLinesAround(): void
    {
        $lineA                   = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineA->lineNumberBefore = 100;
        $lineA->lineNumberAfter  = 100;

        $lineB                   = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineB->lineNumberBefore = 101;
        $lineB->lineNumberAfter  = 101;

        $lineC                   = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineC->lineNumberBefore = 102;
        $lineC->lineNumberAfter  = 102;

        $lineD                   = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineD->lineNumberBefore = 103;
        $lineD->lineNumberAfter  = 103;

        $block        = new DiffBlock();
        $block->lines = [$lineA, $lineB, $lineC, $lineD];

        $file                 = new DiffFile();
        $file->filePathBefore = '/path/to/file/foobar.txt';
        $file->filePathAfter  = '/path/to/file/foobar.txt';
        $file->addBlock($block);

        // match line 100 => expect before: 100, after: 101
        static::assertSame(
            ['before' => [$lineA], 'after' => [$lineB]],
            $this->finder->findLinesAround($file, new LineReference('', 100, 0, 100), 1)
        );

        // match line 101 => expect before:  101, after: 102
        static::assertSame(
            ['before' => [$lineB], 'after' => [$lineC]],
            $this->finder->findLinesAround($file, new LineReference('', 101, 0, 101), 1)
        );

        // match line 101 => expect before:  101, after: 102, margin 2
        static::assertSame(
            ['before' => [$lineA, $lineB], 'after' => [$lineC, $lineD]],
            $this->finder->findLinesAround($file, new LineReference('', 101, 0, 101), 2)
        );

        static::assertNull($this->finder->findLinesAround($file, new LineReference('', 105, 0, 105), 1));
    }

    /**
     * @covers ::findLineInLines
     */
    public function testFindLineInLines(): void
    {
        $lineA                   = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineA->lineNumberBefore = 100;
        $lineA->lineNumberAfter  = 100;

        $lineB                  = new DiffLine(DiffLine::STATE_ADDED, []);
        $lineB->lineNumberAfter = 101;

        $lineC                   = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineC->lineNumberBefore = 101;
        $lineC->lineNumberAfter  = 102;

        $lineD                   = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineD->lineNumberBefore = 102;
        $lineD->lineNumberAfter  = 103;

        $lines = [$lineA, $lineB, $lineC, $lineD];

        static::assertSame($lineA, $this->finder->findLineInLines($lines, new LineReference('', 100, 0, 100)));
        static::assertSame($lineB, $this->finder->findLineInLines($lines, new LineReference('', 100, 1, 101)));
        static::assertSame($lineA, $this->finder->findLineInLines($lines, new LineReference('', 100, 4, 100)));
        static::assertSame($lineC, $this->finder->findLineInLines($lines, new LineReference('', 101, 1, 102)));
        static::assertSame($lineB, $this->finder->findLineInLines($lines, new LineReference('', 0, 0, 101)));
        static::assertNull($this->finder->findLineInLines($lines, new LineReference('', 103, 0, 103)));
    }

    /**
     * @covers ::findLineInBlock
     */
    public function testFineLineInBlockForLines(): void
    {
        $line                   = new DiffLine(DiffLine::STATE_ADDED, []);
        $line->lineNumberBefore = 100;
        $line->lineNumberAfter  = 100;

        $block        = new DiffBlock();
        $block->lines = [99 => $line];

        $file                 = new DiffFile();
        $file->filePathBefore = '/path/to/file/foobar.txt';
        $file->filePathAfter  = '/path/to/file/foobar.txt';

        static::assertNull($this->finder->findLineInBlock($file, $block, new LineReference('', 99, 0, 99)));
        static::assertSame($line, $this->finder->findLineInBlock($file, $block, new LineReference('', 100, 0, 100)));
    }
}

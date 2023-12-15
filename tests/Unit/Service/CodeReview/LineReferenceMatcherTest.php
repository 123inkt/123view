<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview;

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

        $reference = new LineReference(null, '/file/path', 10, 20, 123);

        static::assertSame($line, $this->matcher->exactMatch([$line], $reference));
    }

    public function testExactMatchRemovedLine(): void
    {
        $line                   = new DiffLine(DiffLine::STATE_CHANGED, []);
        $line->lineNumberBefore = 123;

        $reference = new LineReference(null, '/file/path', 123, 0, 0);

        static::assertSame($line, $this->matcher->exactMatch([$line], $reference));
    }

    public function testExactMatchModifiedLine(): void
    {
        $line                   = new DiffLine(DiffLine::STATE_CHANGED, []);
        $line->lineNumberBefore = 123;
        $line->lineNumberAfter  = 456;

        $reference = new LineReference(null, '/file/path', 123, 0, 456);

        static::assertSame($line, $this->matcher->exactMatch([$line], $reference));
    }

    public function testExactMatchNoMatch(): void
    {
        $line                   = new DiffLine(DiffLine::STATE_CHANGED, []);
        $line->lineNumberBefore = 123;
        $line->lineNumberAfter  = 456;

        $reference = new LineReference(null, '/file/path', 123, 456, 789);

        static::assertNull($this->matcher->exactMatch([$line], $reference));
    }

    public function testBestEffortMatch(): void
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

        static::assertSame($lineA, $this->matcher->bestEffortMatch($lines, new LineReference(null, '', 100, 0, 100)));
        static::assertSame($lineB, $this->matcher->bestEffortMatch($lines, new LineReference(null, '', 100, 1, 101)));
        static::assertSame($lineA, $this->matcher->bestEffortMatch($lines, new LineReference(null, '', 100, 4, 100)));
        static::assertSame($lineC, $this->matcher->bestEffortMatch($lines, new LineReference(null, '', 101, 1, 102)));
        static::assertSame($lineB, $this->matcher->bestEffortMatch($lines, new LineReference(null, '', 0, 0, 101)));
        static::assertNull($this->matcher->bestEffortMatch($lines, new LineReference(null, '', 103, 0, 103)));
    }
}

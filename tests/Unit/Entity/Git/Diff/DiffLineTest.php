<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DiffLine::class)]
class DiffLineTest extends AbstractTestCase
{
    public function testConstruct(): void
    {
        $changes = [new DiffChange(DiffChange::REMOVED, 'foobar')];
        $line    = new DiffLine(DiffLine::STATE_ADDED, $changes);

        static::assertSame(DiffLine::STATE_ADDED, $line->state);
        static::assertSame($changes, $line->changes->toArray());
    }

    public function testIsEmpty(): void
    {
        static::assertTrue((new DiffLine(DiffLine::STATE_EMPTY, []))->isEmpty());
        static::assertFalse((new DiffLine(DiffLine::STATE_CHANGED, []))->isEmpty());
    }

    public function testGetLine(): void
    {
        $changes = [new DiffChange(DiffChange::UNCHANGED, 'foo'), new DiffChange(DiffChange::ADDED, 'bar')];
        $line    = new DiffLine(DiffLine::STATE_CHANGED, $changes);

        static::assertSame('foobar', $line->getLine());
    }
}

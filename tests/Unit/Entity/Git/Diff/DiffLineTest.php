<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Git\Diff;

use DR\GitCommitNotification\Entity\Git\Diff\DiffChange;
use DR\GitCommitNotification\Entity\Git\Diff\DiffLine;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Git\Diff\DiffLine
 */
class DiffLineTest extends AbstractTestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $changes = [new DiffChange(DiffChange::REMOVED, 'foobar')];
        $line    = new DiffLine(DiffLine::STATE_ADDED, $changes);

        static::assertSame(DiffLine::STATE_ADDED, $line->state);
        static::assertSame($changes, $line->changes->toArray());
    }

    /**
     * @covers ::getLine
     */
    public function testGetLine(): void
    {
        $changes = [new DiffChange(DiffChange::UNCHANGED, 'foo'), new DiffChange(DiffChange::ADDED, 'bar')];
        $line    = new DiffLine(DiffLine::STATE_CHANGED, $changes);

        static::assertSame('foobar', $line->getLine());
    }
}

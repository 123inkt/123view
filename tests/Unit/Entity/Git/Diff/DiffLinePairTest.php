<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Git\Diff;

use DR\GitCommitNotification\Entity\Git\Diff\DiffLine;
use DR\GitCommitNotification\Entity\Git\Diff\DiffLinePair;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Git\Diff\DiffLinePair
 */
class DiffLinePairTest extends AbstractTestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_REMOVED, []);
        $lineB = new DiffLine(DiffLine::STATE_ADDED, []);

        $pair = new DiffLinePair($lineA, $lineB);
        static::assertSame($pair->removed, $lineA);
        static::assertSame($pair->added, $lineB);
    }
}

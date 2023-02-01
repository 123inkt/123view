<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffChangeCollection;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Git\Diff\DiffChangeBundler;
use DR\Review\Git\Diff\DiffLineDiffer;
use DR\Review\Tests\AbstractTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Git\Diff\DiffLineDiffer
 * @covers ::__construct
 */
class DiffLineDifferTest extends AbstractTestCase
{
    private DiffChangeBundler&MockObject $changeBundler;
    private DiffLineDiffer               $differ;

    protected function setUp(): void
    {
        parent::setUp();
        $this->changeBundler = $this->createMock(DiffChangeBundler::class);
        $this->differ        = new DiffLineDiffer($this->changeBundler);
    }

    /**
     * @covers ::diff
     */
    public function testDiffInvalidChangesShouldThrowException(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_REMOVED, []);
        $lineB = new DiffLine(DiffLine::STATE_ADDED, []);

        $this->expectException(InvalidArgumentException::class);
        $this->differ->diff($lineA, $lineB);
    }

    /**
     * @covers ::diff
     */
    public function testDiff(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'foobar')]);
        $lineB = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, ' foobar ')]);

        $changeA = new DiffChange(DiffChange::UNCHANGED, 'unchanged');
        $changeB = new DiffChange(DiffChange::REMOVED, 'removed');
        $changeC = new DiffChange(DiffChange::ADDED, 'added');
        $changes = [$changeA, $changeB, $changeC];

        $this->changeBundler->expects(self::once())->method('bundle')->willReturn(new DiffChangeCollection($changes));

        $this->differ->diff($lineA, $lineB);
        static::assertEquals([$changeA, $changeB], $lineA->changes->toArray());
        static::assertEquals([$changeA, $changeC], $lineB->changes->toArray());
    }
}

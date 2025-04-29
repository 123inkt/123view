<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Diff\Optimizer;

use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Service\Git\Diff\Optimizer\DiffLineStateDeterminator;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(DiffLineStateDeterminator::class)]
class DiffLineStateDeterminatorTest extends AbstractTestCase
{
    private DiffLineStateDeterminator $determinator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->determinator = new DiffLineStateDeterminator();
    }

    public function testDetermineStateEmptyShouldBeUnchanged(): void
    {
        static::assertSame(DiffLine::STATE_UNCHANGED, $this->determinator->determineState([]));
    }

    public function testDetermineStateMultiTypeShouldBeChanged(): void
    {
        $changes = [
            new DiffChange(DiffChange::ADDED, 'code'),
            new DiffChange(DiffChange::REMOVED, 'code'),
            new DiffChange(DiffChange::UNCHANGED, 'code'),
        ];

        static::assertSame(DiffLine::STATE_CHANGED, $this->determinator->determineState($changes));
    }

    #[DataProvider('dataProvider')]
    public function testDetermineStateSingleType(int $changeType, int $expectedType): void
    {
        static::assertSame($expectedType, $this->determinator->determineState([new DiffChange($changeType, 'code')]));
    }

    /**
     * @return array<array<int, int>>
     */
    public static function dataProvider(): array
    {
        return [
            [DiffChange::ADDED, DiffLine::STATE_ADDED],
            [DiffChange::REMOVED, DiffLine::STATE_REMOVED],
            [DiffChange::UNCHANGED, DiffLine::STATE_INLINED],
            [DiffChange::NEWLINE, DiffLine::STATE_CHANGED],
        ];
    }
}

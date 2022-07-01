<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Git\Diff;

use DR\GitCommitNotification\Entity\Git\Diff\DiffChange;
use DR\GitCommitNotification\Entity\Git\Diff\DiffLine;
use DR\GitCommitNotification\Git\Diff\DiffLineDiffer;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use InvalidArgumentException;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Git\Diff\DiffLineDiffer
 */
class DiffLineDifferTest extends AbstractTestCase
{
    private DiffLineDiffer $differ;

    protected function setUp(): void
    {
        parent::setUp();
        $this->differ = new DiffLineDiffer();
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
    public function testDiffSkipDifferIfOnlyWhiteSpaceChanges(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'foobar')]);
        $lineB = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, ' foobar ')]);

        $this->differ->diff($lineA, $lineB);
        static::assertSame('foobar', $lineA->changes->first()->code);
        static::assertSame(' foobar ', $lineB->changes->first()->code);
    }

    /**
     * @covers ::diff
     */
    public function testDiffShouldShouldSkipAbsentSuffix(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'prefix foo')]);
        $lineB = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'prefix bar')]);

        $this->differ->diff($lineA, $lineB);

        $expected = [
            new DiffChange(DiffChange::UNCHANGED, 'prefix '),
            new DiffChange(DiffChange::REMOVED, 'foo')
        ];
        static::assertEquals($expected, $lineA->changes->toArray());

        $expected = [
            new DiffChange(DiffChange::UNCHANGED, 'prefix '),
            new DiffChange(DiffChange::ADDED, 'bar')
        ];
        static::assertEquals($expected, $lineB->changes->toArray());
    }

    /**
     * @covers ::diff
     */
    public function testDiffShouldSplitSimilarPrefixAndSuffix(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'prefix foo suffix')]);
        $lineB = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'prefix bar suffix')]);

        $this->differ->diff($lineA, $lineB);

        $expected = [
            new DiffChange(DiffChange::UNCHANGED, 'prefix '),
            new DiffChange(DiffChange::REMOVED, 'foo'),
            new DiffChange(DiffChange::UNCHANGED, ' suffix')
        ];
        static::assertEquals($expected, $lineA->changes->toArray());

        $expected = [
            new DiffChange(DiffChange::UNCHANGED, 'prefix '),
            new DiffChange(DiffChange::ADDED, 'bar'),
            new DiffChange(DiffChange::UNCHANGED, ' suffix')
        ];
        static::assertEquals($expected, $lineB->changes->toArray());
    }

    /**
     * @covers ::diff
     */
    public function testPrefixAndSuffixOverlapWithAddition(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'FactoryInterface $factory)')]);
        $lineB = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'FactoryInterface $factory, Repository $repository)')]);

        $this->differ->diff($lineA, $lineB);

        $expected = [new DiffChange(DiffChange::REMOVED, 'FactoryInterface $factory)')];
        static::assertEquals($expected, $lineA->changes->toArray());

        $expected = [
            new DiffChange(DiffChange::UNCHANGED, 'FactoryInterface $factory'),
            new DiffChange(DiffChange::ADDED, ', Repository $repository'),
            new DiffChange(DiffChange::UNCHANGED, ')'),
        ];
        static::assertEquals($expected, $lineB->changes->toArray());
    }

    /**
     * @covers ::diff
     */
    public function testPrefixAndSuffixOverlapWithRemoval(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'FactoryInterface $factory, Repository $repository)')]);
        $lineB = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'FactoryInterface $factory)')]);

        $this->differ->diff($lineA, $lineB);

        $expected = [
            new DiffChange(DiffChange::UNCHANGED, 'FactoryInterface $factory'),
            new DiffChange(DiffChange::REMOVED, ', Repository $repository'),
            new DiffChange(DiffChange::UNCHANGED, ')'),
        ];
        static::assertEquals($expected, $lineA->changes->toArray());

        $expected = [new DiffChange(DiffChange::ADDED, 'FactoryInterface $factory)')];
        static::assertEquals($expected, $lineB->changes->toArray());
    }
}

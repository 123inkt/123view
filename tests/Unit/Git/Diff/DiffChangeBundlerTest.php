<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Git\Diff;

use DR\GitCommitNotification\Entity\Git\Diff\DiffChange;
use DR\GitCommitNotification\Entity\Git\Diff\DiffChangeCollection;
use DR\GitCommitNotification\Git\Diff\DiffChangeBundler;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Git\Diff\DiffChangeBundler
 */
class DiffChangeBundlerTest extends AbstractTestCase
{
    private DiffChangeBundler $bundler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bundler = new DiffChangeBundler();
    }

    /**
     * @covers ::bundle
     * @covers ::mergePrefix
     * @covers ::mergeSuffix
     * @covers ::getPrevious
     * @covers ::getNextNext
     */
    public function testBundleSingleChange(): void
    {
        $changes  = [new DiffChange(DiffChange::REMOVED, 'This-was-unchanged')];
        $expected = $changes;

        $changes = $this->bundler->bundle(new DiffChangeCollection($changes));
        static::assertEquals($expected, $changes->toArray());
    }

    /**
     * @covers ::bundle
     * @covers ::mergePrefix
     * @covers ::mergeSuffix
     * @covers ::getPrevious
     * @covers ::getNextNext
     */
    public function testBundleAddedRemovedWithoutUnchanged(): void
    {
        $changes = [
            new DiffChange(DiffChange::REMOVED, 'This-was-removed!'),
            new DiffChange(DiffChange::ADDED, 'This-was-added!')
        ];

        $expected = [
            new DiffChange(DiffChange::UNCHANGED, 'This-was-'),
            new DiffChange(DiffChange::REMOVED, 'remov'),
            new DiffChange(DiffChange::ADDED, 'add'),
            new DiffChange(DiffChange::UNCHANGED, 'ed!')
        ];

        $changes = $this->bundler->bundle(new DiffChangeCollection($changes));
        static::assertEquals($expected, $changes->toArray());
    }

    /**
     * @covers ::bundle
     * @covers ::mergePrefix
     * @covers ::mergeSuffix
     * @covers ::getPrevious
     * @covers ::getNextNext
     */
    public function testBundleAddedRemovedWithUnchanged(): void
    {
        $changes = [
            new DiffChange(DiffChange::UNCHANGED, 'This'),
            new DiffChange(DiffChange::REMOVED, '-was-removed!'),
            new DiffChange(DiffChange::ADDED, '-was-added!')
        ];

        $expected = [
            new DiffChange(DiffChange::UNCHANGED, 'This-was-'),
            new DiffChange(DiffChange::REMOVED, 'remov'),
            new DiffChange(DiffChange::ADDED, 'add'),
            new DiffChange(DiffChange::UNCHANGED, 'ed!')
        ];

        $changes = $this->bundler->bundle(new DiffChangeCollection($changes));
        static::assertEquals($expected, $changes->toArray());
    }

    /**
     * @covers ::bundle
     * @covers ::mergePrefix
     * @covers ::mergeSuffix
     * @covers ::getPrevious
     * @covers ::getNextNext
     */
    public function testBundleAddedRemovedWithTrailingUnchanged(): void
    {
        $changes = [
            new DiffChange(DiffChange::UNCHANGED, 'This'),
            new DiffChange(DiffChange::REMOVED, '-was-removed'),
            new DiffChange(DiffChange::ADDED, '-was-added'),
            new DiffChange(DiffChange::UNCHANGED, '!'),
        ];

        $expected = [
            new DiffChange(DiffChange::UNCHANGED, 'This-was-'),
            new DiffChange(DiffChange::REMOVED, 'remov'),
            new DiffChange(DiffChange::ADDED, 'add'),
            new DiffChange(DiffChange::UNCHANGED, 'ed!')
        ];

        $changes = $this->bundler->bundle(new DiffChangeCollection($changes));
        static::assertEquals($expected, $changes->toArray());
    }

    /**
     * @covers ::bundle
     * @covers ::mergePrefix
     * @covers ::mergeSuffix
     * @covers ::getPrevious
     * @covers ::getNextNext
     */
    public function testBundleRemovedEntireLine(): void
    {
        $changes = [
            new DiffChange(DiffChange::REMOVED, 'was-removed'),
            new DiffChange(DiffChange::ADDED, ''),
        ];

        $expected = [
            new DiffChange(DiffChange::REMOVED, 'was-removed')
        ];

        $changes = $this->bundler->bundle(new DiffChangeCollection($changes));
        static::assertEquals($expected, $changes->toArray());
    }
}

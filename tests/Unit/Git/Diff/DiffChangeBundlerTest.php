<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Git\Diff\DiffChangeBundler;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Git\Diff\DiffChangeBundler
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
     */
    public function testBundleAddedRemovedWithoutUnchanged(): void
    {
        $expected = [
            new DiffChange(DiffChange::UNCHANGED, 'This-was-'),
            new DiffChange(DiffChange::REMOVED, 'remov'),
            new DiffChange(DiffChange::ADDED, 'add'),
            new DiffChange(DiffChange::UNCHANGED, 'ed!')
        ];

        $changes = $this->bundler->bundle(
            new DiffChange(DiffChange::REMOVED, 'This-was-removed!'),
            new DiffChange(DiffChange::ADDED, 'This-was-added!')
        );
        static::assertEquals($expected, $changes->toArray());
    }

    /**
     * @covers ::bundle
     * @covers ::mergePrefix
     * @covers ::mergeSuffix
     */
    public function testBundleAddedRemovedWithUnchanged(): void
    {
        $expected = [
            new DiffChange(DiffChange::REMOVED, 'is-remov'),
            new DiffChange(DiffChange::ADDED, 'was-add'),
            new DiffChange(DiffChange::UNCHANGED, 'ed!')
        ];

        $changes = $this->bundler->bundle(new DiffChange(DiffChange::REMOVED, 'is-removed!'), new DiffChange(DiffChange::ADDED, 'was-added!'));
        static::assertEquals($expected, $changes->toArray());
    }

    /**
     * @covers ::bundle
     * @covers ::mergePrefix
     * @covers ::mergeSuffix
     */
    public function testBundleRemovedEntireLine(): void
    {
        $expected = [
            new DiffChange(DiffChange::REMOVED, 'was-removed')
        ];

        $changes = $this->bundler->bundle(new DiffChange(DiffChange::REMOVED, 'was-removed'), new DiffChange(DiffChange::ADDED, ''));
        static::assertEquals($expected, $changes->toArray());
    }

    public function testMerge(): void
    {
        $before = new DiffChange(
            DiffChange::REMOVED,
            'public function addAccountRequest(IPAddress $ipAddress, $email, $type, $shoppingCartId = 0): void'
        );
        $after  = new DiffChange(
            DiffChange::ADDED,
            'public function addAccountRequest(IPAddress $ipAddress, string $email, string $type, int $shoppingCartId = 0): void'
        );

        $result = $this->bundler->mergeChange($before, $after);
    }
}

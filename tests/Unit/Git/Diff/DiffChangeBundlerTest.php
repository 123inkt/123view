<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Git\Diff;

use cogpowered\FineDiff\Diff;
use cogpowered\FineDiff\Parser\OpcodesInterface;
use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Git\Diff\DiffChangeBundler;
use DR\Review\Service\Git\Diff\DiffOpcodeTransformer;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Git\Diff\DiffChangeBundler
 */
class DiffChangeBundlerTest extends AbstractTestCase
{
    private DiffOpcodeTransformer&MockObject $opcodeTransformer;
    private DiffChangeBundler                $bundler;

    protected function setUp(): void
    {
        parent::setUp();
        $opcodes = $this->createMock(OpcodesInterface::class);
        $opcodes->method('generate')->willReturn('opcodes');
        $diff = $this->createMock(Diff::class);
        $diff->method('getOpcodes')->willReturn($opcodes);
        $this->opcodeTransformer = $this->createMock(DiffOpcodeTransformer::class);
        $this->bundler           = new DiffChangeBundler($diff, $this->opcodeTransformer);
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

        $this->opcodeTransformer->expects(self::once())
            ->method('transform')
            ->with('remov', 'opcodes')
            ->willReturn([new DiffChange(DiffChange::REMOVED, 'remov'), new DiffChange(DiffChange::ADDED, 'add')]);

        $changes = $this->bundler->bundle(
            new DiffChange(DiffChange::REMOVED, 'This-was-removed!'),
            new DiffChange(DiffChange::ADDED, 'This-was-added!')
        );
        static::assertEquals($expected, $changes->toArray());
    }
}

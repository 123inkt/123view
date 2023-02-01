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
 * @covers ::__construct
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
            new DiffChange(DiffChange::UNCHANGED, 'My '),
            new DiffChange(DiffChange::REMOVED, 'very first '),
            new DiffChange(DiffChange::ADDED, 'long '),
            new DiffChange(DiffChange::UNCHANGED, 'line')
        ];

        $this->opcodeTransformer->expects(self::once())
            ->method('transform')
            ->with('very first ', 'opcodes')
            ->willReturn([new DiffChange(DiffChange::REMOVED, 'very first '), new DiffChange(DiffChange::ADDED, 'long ')]);

        $changes = $this->bundler->bundle(
            new DiffChange(DiffChange::REMOVED, 'My very first line'),
            new DiffChange(DiffChange::ADDED, 'My first long line')
        );
        static::assertEquals($expected, $changes->toArray());
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Git\Diff;

use cogpowered\FineDiff\Diff;
use cogpowered\FineDiff\Parser\OpcodesInterface;
use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffChangeCollection;
use DR\Review\Git\Diff\DiffChangeBundler;
use DR\Review\Git\Diff\Opcode\DiffChangeFactory;
use DR\Review\Git\Diff\Opcode\DiffChangeOptimizer;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Git\Diff\DiffChangeBundler
 * @covers ::__construct
 */
class DiffChangeBundlerTest extends AbstractTestCase
{
    private Diff&MockObject                $fineDiff;
    private DiffChangeFactory&MockObject   $changeFactory;
    private DiffChangeOptimizer&MockObject $changeOptimizer;
    private DiffChangeBundler              $changeBundler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fineDiff        = $this->createMock(Diff::class);
        $this->changeFactory   = $this->createMock(DiffChangeFactory::class);
        $this->changeOptimizer = $this->createMock(DiffChangeOptimizer::class);
        $this->changeBundler   = new DiffChangeBundler($this->fineDiff, $this->changeFactory, $this->changeOptimizer);
    }

    /**
     * @covers ::bundle
     */
    public function testBundle(): void
    {
        $changeA    = new DiffChange(DiffChange::REMOVED, 'removed');
        $changeB    = new DiffChange(DiffChange::REMOVED, 'added');
        $changeC    = new DiffChange(DiffChange::UNCHANGED, 'unchanged');
        $collection = new DiffChangeCollection();

        $opcodes = $this->createMock(OpcodesInterface::class);
        $opcodes->method('generate')->willReturn('opcodes');
        $this->fineDiff->expects(self::once())->method('getOpcodes')->with('removed', 'added')->willReturn($opcodes);
        $this->changeFactory->expects(self::once())->method('createFromOpcodes')->with('removed', 'opcodes')->willReturn([$changeC]);
        $this->changeOptimizer->expects(self::once())->method('optimize')->with([$changeC])->willReturn($collection);

        static::assertSame($collection, $this->changeBundler->bundle($changeA, $changeB));
    }
}

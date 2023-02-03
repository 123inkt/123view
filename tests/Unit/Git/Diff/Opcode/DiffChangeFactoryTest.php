<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Git\Diff\Opcode;

use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Git\Diff\Opcode\DiffChangeFactory;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Git\Diff\Opcode\DiffChangeFactory
 */
class DiffChangeFactoryTest extends AbstractTestCase
{
    private DiffChangeFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new DiffChangeFactory();
    }

    /**
     * @covers ::transform
     */
    public function testTransform(): void
    {
        $lineBefore = 'my first greatest line';
        // $lineAfter  = 'my very first line';
        $opcodes = "c3i5:very c6d9c4";

        $expected = [
            new DiffChange(DiffChange::UNCHANGED, 'my '),
            new DiffChange(DiffChange::ADDED, 'very '),
            new DiffChange(DiffChange::UNCHANGED, 'first '),
            new DiffChange(DiffChange::REMOVED, 'greatest '),
            new DiffChange(DiffChange::UNCHANGED, 'line'),
        ];

        static::assertEquals($expected, $this->factory->createFromOpcodes($lineBefore, $opcodes));
    }
}

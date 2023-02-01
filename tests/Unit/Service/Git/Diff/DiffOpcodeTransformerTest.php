<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Service\Git\Diff\DiffOpcodeTransformer;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Service\Git\Diff\DiffOpcodeTransformer
 */
class DiffOpcodeTransformerTest extends AbstractTestCase
{
    private DiffOpcodeTransformer $transformer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transformer = new DiffOpcodeTransformer();
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

        static::assertEquals($expected, $this->transformer->transform($lineBefore, $opcodes));
    }
}

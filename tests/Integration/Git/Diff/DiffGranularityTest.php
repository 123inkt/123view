<?php
declare(strict_types=1);

namespace DR\Review\Tests\Integration\Git\Diff;

use cogpowered\FineDiff\Diff;
use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Git\Diff\DiffGranularity;
use DR\Review\Service\Git\Diff\DiffOpcodeTransformer;
use DR\Review\Tests\AbstractTestCase;
use Generator;

/**
 * @coversNothing
 */
class DiffGranularityTest extends AbstractTestCase
{
    private Diff                  $diff;
    private DiffOpcodeTransformer $transformer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->diff        = new Diff(new DiffGranularity());
        $this->transformer = new DiffOpcodeTransformer();
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGranularity(string $before, string $after, string $expected): void
    {
        $opcodes = $this->diff->getOpcodes($before, $after)->generate();
        $changes = $this->transformer->transform($before, $opcodes);

        $result = '';
        foreach ($changes as $change) {
            if ($change->type === DiffChange::ADDED) {
                $result .= ' +';
            } elseif ($change->type === DiffChange::REMOVED) {
                $result .= ' -';
            }
            $result .= $change->code;
        }
        static::assertSame($expected, $result);
    }

    public function dataProvider(): Generator
    {
        yield ['OrNull(self::PRICE', '(self::PRICE, 0.0', ''];
    }
}

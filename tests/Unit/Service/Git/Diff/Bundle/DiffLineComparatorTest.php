<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Diff\Bundle;

use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Service\Git\Diff\Bundle\DiffLineComparator;
use DR\Review\Service\Git\Diff\Bundle\DiffLineCompareResult;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Service\Git\Diff\Bundle\DiffLineComparator
 */
class DiffLineComparatorTest extends AbstractTestCase
{
    /**
     * @covers ::compare
     * @covers ::similarity
     * @dataProvider dataProvider
     */
    public function testCompareEqualStrings(string $codeA, string $codeB, int $removals, int $additions, int $whitespace, int $levenshtein): void
    {
        $lineA = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, $codeA)]);
        $lineB = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, $codeB)]);

        $comparator = new DiffLineComparator();
        $result     = $comparator->compare($lineA, $lineB);

        $expected = new DiffLineCompareResult($removals, $additions, $whitespace, $levenshtein);
        static::assertEquals($expected, $result);
    }

    /**
     * @return array<string, array<string|int>>
     */
    public static function dataProvider(): array
    {
        return [
            'equals strings'          => [' this line is equal ', ' this line is equal ', 0, 0, 0, 0],
            'only whitespace changes' => [' this line is equal ', 'this line is equal', 0, 0, 2, 0],
            'only text additions'     => [' this line is equal ', ' this line isn\'t equal ', 0, 3, 0, 3],
            'only text removals'      => [' this line isn\'t equal ', ' this line is equal ', 3, 0, 0, 3],
            'text changes'            => ['this line is', 'completely different ', 10, 19, 0, 16],
            'many changes'            => ['this line is   ', '   completely    different ', 10, 19, 3, 16],
        ];
    }
}

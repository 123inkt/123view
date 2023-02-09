<?php
declare(strict_types=1);

namespace DR\Review\Tests\Integration\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Git\Diff\DiffChangeBundler;
use DR\Review\Tests\AbstractKernelTestCase;
use DR\Review\Utility\Assert;
use Exception;
use Generator;

/**
 * @coversNothing
 */
class DiffChangeBundlerTest extends AbstractKernelTestCase
{
    private DiffChangeBundler $bundler;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->bundler = Assert::instanceOf(DiffChangeBundler::class, self::getContainer()->get(DiffChangeBundler::class));
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string[] $expected
     */
    public function testBundle(string $before, string $after, array $expected): void
    {
        $changeBefore = new DiffChange(DiffChange::REMOVED, $before);
        $changeAfter  = new DiffChange(DiffChange::ADDED, $after);
        $changes      = $this->bundler->bundle($changeBefore, $changeAfter);

        // format to string
        $actual = [];
        foreach ($changes as $change) {
            if ($change->type === DiffChange::REMOVED) {
                $actual[] = '-' . $change->code;
            } elseif ($change->type === DiffChange::ADDED) {
                $actual[] = '+' . $change->code;
            } else {
                $actual[] = $change->code;
            }
        }

        static::assertSame($expected, $actual);
    }

    /**
     * @return Generator<array<string|string[]>>
     */
    public function dataProvider(): Generator
    {
        yield [
            'public function getter($modelId, $timestamp)',
            'public function getter(int $modelId, int $timestamp): ?array',
            ['public function getter(', '+int ', '$modelId, ', '+int ', '$timestamp)', '+: ?array']
        ];
    }
}

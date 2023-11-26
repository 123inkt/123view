<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Log;

use DateTimeImmutable;
use DateTimeZone;
use DR\Review\Service\Git\Log\GitLogCommandBuilder;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitLogCommandBuilder::class)]
class GitLogCommandBuilderTest extends AbstractTestCase
{
    private const DEFAULTS = [
        'git',
        'log',
    ];

    private GitLogCommandBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new GitLogCommandBuilder('git');
    }

    public function testBuildDefaults(): void
    {
        static::assertSame(self::DEFAULTS, $this->builder->build());
    }

    public function testBuildSpace(): void
    {
        $actual = $this->builder
            ->ignoreSpaceChange()
            ->ignoreBlankLines()
            ->ignoreAllSpace()
            ->ignoreSpaceAtEol()
            ->ignoreCrAtEol()
            ->build();

        static::assertSame(
            array_merge(
                self::DEFAULTS,
                [
                    '--ignore-space-change',
                    '--ignore-blank-lines',
                    '--ignore-all-space',
                    '--ignore-space-at-eol',
                    '--ignore-cr-at-eol',
                ]
            ),
            $actual
        );
    }

    /**
     * @throws Exception
     */
    public function testBuildFormatting(): void
    {
        $actual = $this->builder
            ->remotes()
            ->topoOrder()
            ->patch()
            ->diffAlgorithm("foobar")
            ->decorate("tree")
            ->format("format")
            ->since(new DateTimeImmutable('2021-10-18 21:05:00', new DateTimeZone('Europe/Amsterdam')))
            ->until(new DateTimeImmutable('2021-10-18 22:05:00', new DateTimeZone('Europe/Amsterdam')))
            ->noMerges()
            ->reverse()
            ->dateOrder()
            ->maxCount(5)
            ->hashRange('fromHash', 'toHash')
            ->build();

        static::assertSame(
            array_merge(
                self::DEFAULTS,
                [
                    '--remotes',
                    '--topo-order',
                    '--patch',
                    '--diff-algorithm="foobar"',
                    '--decorate="tree"',
                    '--format="format"',
                    '--since="2021-10-18T21:05:00+02:00"',
                    '--until="2021-10-18T22:05:00+02:00"',
                    '--no-merges',
                    '--reverse',
                    '--date-order',
                    '--max-count=5',
                    'fromHash..toHash'
                ]
            ),
            $actual
        );
    }

    public function testCommand(): void
    {
        static::assertSame('log', $this->builder->command());
    }

    public function testToString(): void
    {
        static::assertSame('git log', (string)$this->builder);
    }
}

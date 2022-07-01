<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\Log;

use DateTimeImmutable;
use DateTimeZone;
use DR\GitCommitNotification\Service\Git\Log\GitLogCommandBuilder;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use Exception;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\Log\GitLogCommandBuilder
 * @covers ::__construct
 */
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

    /**
     * @covers ::build
     */
    public function testBuildDefaults(): void
    {
        static::assertSame(self::DEFAULTS, $this->builder->start()->build());
    }

    /**
     * @covers ::start
     * @covers ::ignoreSpaceChange
     * @covers ::ignoreBlankLines
     * @covers ::ignoreAllSpace
     * @covers ::ignoreSpaceAtEol
     * @covers ::ignoreCrAtEol
     * @covers ::build
     */
    public function testBuildSpace(): void
    {
        $actual = $this->builder
            ->start()
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
     * @covers ::start
     * @covers ::remotes
     * @covers ::topoOrder
     * @covers ::patch
     * @covers ::diffAlgorithm
     * @covers ::decorate
     * @covers ::format
     * @covers ::since
     * @covers ::until
     * @covers ::noMerges
     * @covers ::build
     * @throws Exception
     */
    public function testBuildFormatting(): void
    {
        $actual = $this->builder
            ->start()
            ->remotes()
            ->topoOrder()
            ->patch()
            ->diffAlgorithm("foobar")
            ->decorate("tree")
            ->format("format")
            ->since(new DateTimeImmutable('2021-10-18 21:05:00', new DateTimeZone('Europe/Amsterdam')))
            ->until(new DateTimeImmutable('2021-10-18 22:05:00', new DateTimeZone('Europe/Amsterdam')))
            ->noMerges()
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
                ]
            ),
            $actual
        );
    }

    /**
     * @covers ::__toString
     */
    public function testToString(): void
    {
        static::assertSame('git log', (string)$this->builder->start());
    }
}

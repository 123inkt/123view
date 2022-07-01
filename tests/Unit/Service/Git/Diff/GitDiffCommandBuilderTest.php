<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\Diff;

use DR\GitCommitNotification\Service\Git\Diff\GitDiffCommandBuilder;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\Diff\GitDiffCommandBuilder
 * @covers ::__construct
 */
class GitDiffCommandBuilderTest extends AbstractTestCase
{
    private const DEFAULTS = [
        'git',
        'diff'
    ];

    private GitDiffCommandBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new GitDiffCommandBuilder('git');
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
     * @covers ::hashes
     * @covers ::diffAlgorithm
     */
    public function testBuildOptions(): void
    {
        $actual = $this->builder
            ->start()
            ->hashes('start', 'end')
            ->diffAlgorithm("foobar")
            ->build();

        static::assertSame(
            array_merge(
                self::DEFAULTS,
                [
                    'start',
                    'end',
                    '--diff-algorithm="foobar"'
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
        static::assertSame('git diff', (string)$this->builder->start());
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Diff;

use DR\Review\Service\Git\Diff\GitDiffCommandBuilder;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitDiffCommandBuilder::class)]
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

    public function testBuildOptions(): void
    {
        $actual = $this->builder
            ->hashes('start', 'end')
            ->hash('hash')
            ->unified(5)
            ->diffAlgorithm("foobar")
            ->build();

        static::assertSame(
            array_merge(
                self::DEFAULTS,
                [
                    'start',
                    'end',
                    'hash',
                    '--unified=5',
                    '--diff-algorithm="foobar"'
                ]
            ),
            $actual
        );
    }

    public function testCommand(): void
    {
        static::assertSame('diff', $this->builder->command());
    }

    public function testToString(): void
    {
        static::assertSame('git diff', (string)$this->builder);
    }
}

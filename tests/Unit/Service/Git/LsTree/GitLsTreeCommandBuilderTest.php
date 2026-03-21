<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\LsTree;

use DR\Review\Service\Git\LsTree\GitLsTreeCommandBuilder;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitLsTreeCommandBuilder::class)]
class GitLsTreeCommandBuilderTest extends AbstractTestCase
{
    private const DEFAULTS = [
        'git',
        'ls-tree'
    ];

    private GitLsTreeCommandBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new GitLsTreeCommandBuilder('git');
    }

    public function testBuildDefaults(): void
    {
        static::assertSame(self::DEFAULTS, $this->builder->build());
    }

    public function testBuildOptions(): void
    {
        $actual = $this->builder
            ->hash('hash')
            ->recursive()
            ->nameOnly()
            ->build();

        static::assertSame(
            [
                'git',
                'ls-tree',
                'hash',
                '-r',
                '--name-only',
            ],
            $actual
        );
    }

    public function testBuildWithFiles(): void
    {
        $actual = $this->builder
            ->hash('HEAD')
            ->file('path/to/file.txt', 'another/file.php')
            ->build();

        static::assertSame(
            [
                'git',
                'ls-tree',
                'HEAD',
                '--',
                escapeshellarg('path/to/file.txt'),
                escapeshellarg('another/file.php'),
            ],
            $actual
        );
    }

    public function testFileFiltersEmptyStrings(): void
    {
        $actual = $this->builder
            ->hash('HEAD')
            ->file('path/to/file.txt', '', '   ', 'valid.php')
            ->build();

        static::assertSame(
            [
                'git',
                'ls-tree',
                'HEAD',
                '--',
                escapeshellarg('path/to/file.txt'),
                escapeshellarg('valid.php'),
            ],
            $actual
        );
    }

    public function testCommand(): void
    {
        static::assertSame('ls-tree', $this->builder->command());
    }

    public function testToString(): void
    {
        static::assertSame('git ls-tree', (string)$this->builder);
    }

    public function testToStringWithOptions(): void
    {
        $this->builder
            ->hash('HEAD')
            ->recursive()
            ->nameOnly();

        static::assertSame('git ls-tree HEAD -r --name-only', (string)$this->builder);
    }
}

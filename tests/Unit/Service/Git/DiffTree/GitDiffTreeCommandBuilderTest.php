<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\DiffTree;

use DR\Review\Service\Git\DiffTree\GitDiffTreeCommandBuilder;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitDiffTreeCommandBuilder::class)]
class GitDiffTreeCommandBuilderTest extends AbstractTestCase
{
    private const DEFAULTS = [
        'git',
        'diff-tree'
    ];

    private GitDiffTreeCommandBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new GitDiffTreeCommandBuilder('git');
    }

    public function testBuildDefaults(): void
    {
        static::assertSame(self::DEFAULTS, $this->builder->build());
    }

    public function testBuildOptions(): void
    {
        $actual = $this->builder
            ->noCommitId()
            ->nameOnly()
            ->recurseSubTree()
            ->hash('hash')
            ->build();

        static::assertSame(
            [
                'git',
                'diff-tree',
                '--no-commit-id',
                '--name-only',
                '-r',
                'hash',
            ],
            $actual
        );
    }

    public function testCommand(): void
    {
        static::assertSame('diff-tree', $this->builder->command());
    }

    public function testToString(): void
    {
        static::assertSame('git diff-tree', (string)$this->builder);
    }
}

<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\DiffTree;

use DR\GitCommitNotification\Service\Git\DiffTree\GitDiffTreeCommandBuilder;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\DiffTree\GitDiffTreeCommandBuilder
 * @covers ::__construct
 */
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

    /**
     * @covers ::build
     */
    public function testBuildDefaults(): void
    {
        static::assertSame(self::DEFAULTS, $this->builder->build());
    }

    /**
     * @covers ::noCommitId
     * @covers ::nameOnly
     * @covers ::recurseSubTree
     * @covers ::hash
     * @covers ::build
     */
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

    /**
     * @covers ::command
     */
    public function testCommand(): void
    {
        static::assertSame('diff-tree', $this->builder->command());
    }

    /**
     * @covers ::__toString
     */
    public function testToString(): void
    {
        static::assertSame('git diff-tree', (string)$this->builder);
    }
}

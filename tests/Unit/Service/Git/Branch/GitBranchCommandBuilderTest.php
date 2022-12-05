<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Branch;

use DR\Review\Service\Git\Branch\GitBranchCommandBuilder;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Service\Git\Branch\GitBranchCommandBuilder
 * @covers ::__construct
 */
class GitBranchCommandBuilderTest extends AbstractTestCase
{
    private const DEFAULTS = ['git', 'branch'];

    private GitBranchCommandBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new GitBranchCommandBuilder('git');
    }

    /**
     * @covers ::build
     */
    public function testBuildDefaults(): void
    {
        static::assertSame(self::DEFAULTS, $this->builder->build());
    }

    /**
     * @covers ::delete
     * @covers ::build
     */
    public function testBuildMethods(): void
    {
        static::assertSame(['git', 'branch', '-D branchName'], $this->builder->delete('branchName')->build());
    }

    /**
     * @covers ::command
     */
    public function testCommand(): void
    {
        static::assertSame('branch', $this->builder->command());
    }

    /**
     * @covers ::__toString
     */
    public function testToString(): void
    {
        static::assertSame('git branch -D branchName', (string)$this->builder->delete('branchName'));
    }
}

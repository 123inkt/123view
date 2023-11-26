<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Branch;

use DR\Review\Service\Git\Branch\GitBranchCommandBuilder;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitBranchCommandBuilder::class)]
class GitBranchCommandBuilderTest extends AbstractTestCase
{
    private const DEFAULTS = ['git', 'branch'];

    private GitBranchCommandBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new GitBranchCommandBuilder('git');
    }

    public function testBuildDefaults(): void
    {
        static::assertSame(self::DEFAULTS, $this->builder->build());
    }

    public function testBuildMethods(): void
    {
        static::assertSame(['git', 'branch', '-D branchName', '--merged', '-r'], $this->builder->delete('branchName')->merged()->remote()->build());
    }

    public function testCommand(): void
    {
        static::assertSame('branch', $this->builder->command());
    }

    public function testToString(): void
    {
        static::assertSame('git branch -D branchName', (string)$this->builder->delete('branchName'));
    }
}

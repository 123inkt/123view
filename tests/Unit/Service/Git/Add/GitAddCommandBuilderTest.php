<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Add;

use DR\Review\Service\Git\Add\GitAddCommandBuilder;
use DR\Review\Tests\AbstractTestCase;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitAddCommandBuilder::class)]
class GitAddCommandBuilderTest extends AbstractTestCase
{
    private GitAddCommandBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new GitAddCommandBuilder('git');
    }

    public function testBuildWithoutPathsShouldThrow(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('At least one path is required for git add.');

        $this->builder->build();
    }

    public function testPaths(): void
    {
        static::assertSame(
            ['git', 'add', '--', 'first path', '-second-path'],
            $this->builder->paths('first path', '-second-path')->build()
        );
    }

    public function testCommand(): void
    {
        static::assertSame('add', $this->builder->command());
    }

    public function testToString(): void
    {
        static::assertSame('git add -- first second', (string)$this->builder->paths('first', 'second'));
    }

    public function testRequiresShell(): void
    {
        static::assertFalse($this->builder->requiresShell());
    }
}

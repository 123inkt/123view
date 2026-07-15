<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Clone;

use DR\Review\Service\Git\Clone\GitCloneCommandBuilder;
use DR\Review\Tests\AbstractTestCase;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitCloneCommandBuilder::class)]
class GitCloneCommandBuilderTest extends AbstractTestCase
{
    private GitCloneCommandBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new GitCloneCommandBuilder('git');
    }

    public function testCommand(): void
    {
        static::assertSame('clone', $this->builder->command());
    }

    public function testRequiresShell(): void
    {
        static::assertFalse($this->builder->requiresShell());
    }

    public function testBuild(): void
    {
        $result = $this->builder
            ->repository('https://example.com/repo.git')
            ->directory('/tmp/repo')
            ->build();

        static::assertSame(['git', 'clone', '-q', '--end-of-options', 'https://example.com/repo.git', '/tmp/repo'], $result);
    }

    public function testBuildThrowsWhenRepositoryMissing(): void
    {
        $this->expectException(LogicException::class);
        $this->builder->directory('/tmp/repo')->build();
    }

    public function testBuildThrowsWhenDirectoryMissing(): void
    {
        $this->expectException(LogicException::class);
        $this->builder->repository('https://example.com/repo.git')->build();
    }

    public function testGetSensitiveReplacementsWithCredentials(): void
    {
        $this->builder->repository('https://user:pass@example.com/repo.git');
        static::assertSame(
            ['https://user:pass@example.com/repo.git' => 'https://***@example.com/repo.git'],
            $this->builder->getSensitiveReplacements()
        );
    }

    public function testGetSensitiveReplacementsWithoutCredentials(): void
    {
        $this->builder->repository('https://example.com/repo.git');
        static::assertSame([], $this->builder->getSensitiveReplacements());
    }

    public function testToStringRedactsCredentials(): void
    {
        $this->builder->repository('https://user:pass@example.com/repo.git')->directory('/tmp/repo');
        static::assertSame(
            'git clone -q --end-of-options https://***@example.com/repo.git /tmp/repo',
            (string)$this->builder
        );
    }
}

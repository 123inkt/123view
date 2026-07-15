<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Remote;

use DR\Review\Service\Git\Remote\GitRemoteCommandBuilder;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitRemoteCommandBuilder::class)]
class GitRemoteCommandBuilderTest extends AbstractTestCase
{
    private GitRemoteCommandBuilder $builder;

    public function setUp(): void
    {
        parent::setUp();
        $this->builder = new GitRemoteCommandBuilder('git');
    }

    public function testToString(): void
    {
        $urlWithCredentials = 'https://user:pass@example.com/test';

        $command = (string)$this->builder->setUrl('name', 'url')->setUrl('name', $urlWithCredentials);
        static::assertSame('git remote set-url name *************', $command);
    }

    public function testBuild(): void
    {
        static::assertSame(
            ['git', 'remote', 'set-url', 'name', 'url'],
            $this->builder->setUrl('name', 'url')->build()
        );
    }

    public function testRequiresShell(): void
    {
        static::assertFalse($this->builder->requiresShell());
    }

    public function testGetSensitiveReplacementsWithCredentials(): void
    {
        $this->builder->setUrl('origin', 'https://user:pass@example.com/repo.git');
        $replacements = $this->builder->getSensitiveReplacements();
        static::assertSame(
            ['https://user:pass@example.com/repo.git' => 'https://***@example.com/repo.git'],
            $replacements
        );
    }

    public function testGetSensitiveReplacementsWithoutCredentials(): void
    {
        $this->builder->setUrl('origin', 'https://example.com/repo.git');
        static::assertSame([], $this->builder->getSensitiveReplacements());
    }
}

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
        static::assertSame('git remote set-url name https://example.com/test', $command);
    }

    public function testBuild(): void
    {
        static::assertSame(['git', 'remote', 'set-url', 'name', 'url'], $this->builder->setUrl('name', 'url')->build());
    }
}

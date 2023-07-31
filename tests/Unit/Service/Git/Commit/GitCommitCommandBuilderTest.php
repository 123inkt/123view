<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Commit;

use DR\Review\Service\Git\AbstractGitCommandBuilder;
use DR\Review\Service\Git\Commit\GitCommitCommandBuilder;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitCommitCommandBuilder::class)]
#[CoversClass(AbstractGitCommandBuilder::class)]
class GitCommitCommandBuilderTest extends AbstractTestCase
{
    private const DEFAULTS = ['git', 'commit'];

    private GitCommitCommandBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new GitCommitCommandBuilder('git');
    }

    public function testBuildDefaults(): void
    {
        static::assertSame(self::DEFAULTS, $this->builder->build());
    }

    public function testBuild(): void
    {
        static::assertSame(
            ['git', 'commit', '--allow-empty', '-m ' . escapeshellarg('message')],
            $this->builder->allowEmpty()->message('message')->build()
        );
    }
}

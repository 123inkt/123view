<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\CherryPick;

use DR\Review\Service\Git\CherryPick\GitCherryPickCommandBuilder;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitCherryPickCommandBuilder::class)]
class GitCherryPickCommandBuilderTest extends AbstractTestCase
{
    private GitCherryPickCommandBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new GitCherryPickCommandBuilder('git');
    }

    public function testBuildWithOptions(): void
    {
        static::assertSame(
            ['git', 'cherry-pick', '--strategy=strategy', '--abort', '--continue', '--no-commit', 'hashes', '-X', 'theirs'],
            $this->builder->strategy('strategy')->abort()->continue()->noCommit()->hashes(['hashes'])->conflictResolution('theirs')->build()
        );
    }

    public function testHashesReplacesExistingHashes(): void
    {
        // First call sets hash-0 = 'aaa'; second call must unset it and set hash-0 = 'bbb'
        $this->builder->hashes(['aaa']);
        static::assertSame(['git', 'cherry-pick', 'bbb'], $this->builder->hashes(['bbb'])->build());
    }

    public function testRequiresShell(): void
    {
        static::assertFalse($this->builder->requiresShell());
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Fetch;

use DR\Review\Service\Git\Fetch\GitFetchCommandBuilder;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitFetchCommandBuilder::class)]
class GitFetchCommandBuilderTest extends AbstractTestCase
{
    private const DEFAULTS = ['git', 'fetch'];

    private GitFetchCommandBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new GitFetchCommandBuilder('git');
    }

    public function testBuildDefaults(): void
    {
        static::assertSame(self::DEFAULTS, $this->builder->build());
    }

    public function testBuildWithOptions(): void
    {
        static::assertSame(
            ['git', 'fetch', '--verbose', '--prune', '--no-tags', '--all'],
            $this->builder->verbose()->prune()->noTags()->all()->build()
        );
    }

    public function testCommand(): void
    {
        static::assertSame('fetch', $this->builder->command());
    }

    public function testToString(): void
    {
        static::assertSame(
            'git fetch --verbose --prune --all',
            (string)$this->builder->verbose()->prune()->all()
        );
    }
}

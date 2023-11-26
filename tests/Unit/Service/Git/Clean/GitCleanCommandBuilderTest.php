<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Clean;

use DR\Review\Service\Git\Clean\GitCleanCommandBuilder;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitCleanCommandBuilder::class)]
class GitCleanCommandBuilderTest extends AbstractTestCase
{
    private const DEFAULTS = ['git', 'clean'];

    private GitCleanCommandBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new GitCleanCommandBuilder('git');
    }

    public function testBuildDefaults(): void
    {
        static::assertSame(self::DEFAULTS, $this->builder->build());
    }

    public function testBuildWithOptions(): void
    {
        static::assertSame(
            ['git', 'clean', '--force', '-x', '-d'],
            $this->builder->force()->skipIgnoreRules()->recurseDirectories()->build()
        );
    }

    public function testCommand(): void
    {
        static::assertSame('clean', $this->builder->command());
    }

    public function testToString(): void
    {
        static::assertSame(
            'git clean --force -x -d',
            (string)$this->builder->force()->skipIgnoreRules()->recurseDirectories()
        );
    }
}

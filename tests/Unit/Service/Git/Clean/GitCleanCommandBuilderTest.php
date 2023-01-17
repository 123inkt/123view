<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Clean;

use DR\Review\Service\Git\Clean\GitCleanCommandBuilder;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Service\Git\Clean\GitCleanCommandBuilder
 * @covers ::__construct
 */
class GitCleanCommandBuilderTest extends AbstractTestCase
{
    private const DEFAULTS = ['git', 'clean'];

    private GitCleanCommandBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new GitCleanCommandBuilder('git');
    }

    /**
     * @covers ::build
     */
    public function testBuildDefaults(): void
    {
        static::assertSame(self::DEFAULTS, $this->builder->build());
    }

    /**
     * @covers ::force
     * @covers ::skipIgnoreRules
     * @covers ::recurseDirectories
     * @covers ::build
     */
    public function testBuildWithOptions(): void
    {
        static::assertSame(
            ['git', 'clean', '--force', '-x', '-d'],
            $this->builder->force()->skipIgnoreRules()->recurseDirectories()->build()
        );
    }

    /**
     * @covers ::command
     */
    public function testCommand(): void
    {
        static::assertSame('clean', $this->builder->command());
    }

    /**
     * @covers ::__toString
     */
    public function testToString(): void
    {
        static::assertSame(
            'git clean --force -x -d',
            (string)$this->builder->force()->skipIgnoreRules()->recurseDirectories()
        );
    }
}

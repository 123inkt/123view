<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Fetch;

use DR\Review\Service\Git\Fetch\GitFetchCommandBuilder;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Service\Git\Fetch\GitFetchCommandBuilder
 * @covers ::__construct
 */
class GitFetchCommandBuilderTest extends AbstractTestCase
{
    private const DEFAULTS = ['git', 'fetch'];

    private GitFetchCommandBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new GitFetchCommandBuilder('git');
    }

    /**
     * @covers ::build
     */
    public function testBuildDefaults(): void
    {
        static::assertSame(self::DEFAULTS, $this->builder->build());
    }

    /**
     * @covers ::verbose
     * @covers ::all
     * @covers ::prune
     * @covers ::build
     */
    public function testBuildWithOptions(): void
    {
        static::assertSame(
            ['git', 'fetch', '--verbose', '--prune', '--all'],
            $this->builder->verbose()->prune()->all()->build()
        );
    }

    /**
     * @covers ::command
     */
    public function testCommand(): void
    {
        static::assertSame('fetch', $this->builder->command());
    }

    /**
     * @covers ::__toString
     */
    public function testToString(): void
    {
        static::assertSame(
            'git fetch --verbose --prune --all',
            (string)$this->builder->verbose()->prune()->all()
        );
    }
}

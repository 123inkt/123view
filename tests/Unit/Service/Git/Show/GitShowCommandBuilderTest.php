<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Show;

use DR\Review\Service\Git\Show\GitShowCommandBuilder;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Service\Git\Show\GitShowCommandBuilder
 * @covers ::__construct
 */
class GitShowCommandBuilderTest extends AbstractTestCase
{
    private GitShowCommandBuilder $builder;

    public function setUp(): void
    {
        parent::setUp();
        $this->builder = new GitShowCommandBuilder('git');
    }

    /**
     * @covers ::command
     */
    public function testCommand(): void
    {
        static::assertSame('show', $this->builder->command());
    }

    /**
     * @covers ::startPoint
     * @covers ::unified
     * @covers ::file
     * @covers ::build
     */
    public function testBuild(): void
    {
        static::assertSame(
            ['git', 'show', 'foobar', '--unified=5', 'hash:file'],
            $this->builder->startPoint('foobar')->unified(5)->file('hash', 'file')->build()
        );
    }

    /**
     * @covers ::startPoint
     * @covers ::unified
     * @covers ::file
     * @covers ::__toString
     */
    public function testToString(): void
    {
        static::assertSame('git show foobar --unified=5 hash:file', (string)$this->builder->startPoint('foobar')->unified(5)->file('hash', 'file'));
    }
}

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
     * @covers ::ignoreSpaceAtEol
     * @covers ::ignoreCrAtEol
     * @covers ::ignoreSpaceChange
     * @covers ::ignoreAllSpace
     * @covers ::noPatch
     * @covers ::format
     * @covers ::file
     * @covers ::build
     */
    public function testBuild(): void
    {
        static::assertSame(
            [
                'git',
                'show',
                'foobar',
                '--unified=5',
                '--no-patch',
                '--format="format"',
                'hash:file',
                '--ignore-space-at-eol',
                '--ignore-cr-at-eol',
                '--ignore-space-change',
                '--ignore-all-space'
            ],
            $this->builder->startPoint('foobar')
                ->unified(5)
                ->noPatch()
                ->format('format')
                ->file('hash', 'file')
                ->ignoreSpaceAtEol()
                ->ignoreCrAtEol()
                ->ignoreSpaceChange()
                ->ignoreAllSpace()
                ->build()
        );
    }

    /**
     * @covers ::startPoint
     * @covers ::unified
     * @covers ::ignoreSpaceAtEol
     * @covers ::ignoreCrAtEol
     * @covers ::noPatch
     * @covers ::format
     * @covers ::file
     * @covers ::__toString
     */
    public function testToString(): void
    {
        static::assertSame(
            'git show foobar --unified=5 --no-patch --format="format" hash:file --ignore-space-at-eol --ignore-cr-at-eol',
            (string)$this->builder->startPoint('foobar')
                ->unified(5)
                ->noPatch()
                ->format('format')
                ->file('hash', 'file')
                ->ignoreSpaceAtEol()
                ->ignoreCrAtEol()
        );
    }
}

<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\Show;

use DR\GitCommitNotification\Service\Git\Show\GitShowCommandBuilder;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\Show\GitShowCommandBuilder
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
     * @covers ::build
     */
    public function testBuild(): void
    {
        static::assertSame(['git', 'show', 'foobar'], $this->builder->startPoint('foobar')->build());
    }

    /**
     * @covers ::__toString
     */
    public function testToString(): void
    {
        static::assertSame('git show foobar', (string)$this->builder->startPoint('foobar'));
    }
}

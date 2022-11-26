<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\Reset;

use DR\GitCommitNotification\Service\Git\Reset\GitResetCommandBuilder;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\Reset\GitResetCommandBuilder
 * @covers ::__construct
 */
class GitResetCommandBuilderTest extends AbstractTestCase
{
    private GitResetCommandBuilder $builder;

    public function setUp(): void
    {
        parent::setUp();
        $this->builder = new GitResetCommandBuilder('git');
    }

    /**
     * @covers ::command
     */
    public function testCommand(): void
    {
        static::assertSame('reset', $this->builder->command());
    }

    /**
     * @covers ::hard
     * @covers ::build
     */
    public function testBuild(): void
    {
        static::assertSame(['git', 'reset', '--hard'], $this->builder->hard()->build());
    }

    /**
     * @covers ::hard
     * @covers ::__toString
     */
    public function testToString(): void
    {
        static::assertSame('git reset --hard', (string)$this->builder->hard());
    }
}

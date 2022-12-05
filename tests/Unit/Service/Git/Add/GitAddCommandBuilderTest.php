<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Add;

use DR\Review\Service\Git\Add\GitAddCommandBuilder;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Service\Git\Add\GitAddCommandBuilder
 * @covers ::__construct
 */
class GitAddCommandBuilderTest extends AbstractTestCase
{
    private const DEFAULTS = ['git', 'add'];

    private GitAddCommandBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new GitAddCommandBuilder('git');
    }

    /**
     * @covers ::build
     */
    public function testBuildDefaults(): void
    {
        static::assertSame(self::DEFAULTS, $this->builder->build());
    }

    /**
     * @covers ::setPath
     * @covers ::build
     */
    public function testSetPath(): void
    {
        static::assertSame(['git', 'add', '.'], $this->builder->setPath('.')->build());
    }

    /**
     * @covers ::command
     */
    public function testCommand(): void
    {
        static::assertSame('add', $this->builder->command());
    }

    /**
     * @covers ::__toString
     */
    public function testToString(): void
    {
        static::assertSame('git add .', (string)$this->builder->setPath('.'));
    }
}

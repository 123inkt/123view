<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Add;

use DR\Review\Service\Git\Add\GitAddCommandBuilder;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitAddCommandBuilder::class)]
class GitAddCommandBuilderTest extends AbstractTestCase
{
    private const DEFAULTS = ['git', 'add'];

    private GitAddCommandBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new GitAddCommandBuilder('git');
    }

    public function testBuildDefaults(): void
    {
        static::assertSame(self::DEFAULTS, $this->builder->build());
    }

    public function testSetPath(): void
    {
        static::assertSame(['git', 'add', '.'], $this->builder->setPath('.')->build());
    }

    public function testCommand(): void
    {
        static::assertSame('add', $this->builder->command());
    }

    public function testToString(): void
    {
        static::assertSame('git add .', (string)$this->builder->setPath('.'));
    }
}

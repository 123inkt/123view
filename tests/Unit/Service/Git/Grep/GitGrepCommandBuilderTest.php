<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Grep;

use DR\Review\Service\Git\Grep\GitGrepCommandBuilder;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitGrepCommandBuilder::class)]
class GitGrepCommandBuilderTest extends AbstractTestCase
{
    private GitGrepCommandBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new GitGrepCommandBuilder('git');
    }

    public function testCommand(): void
    {
        static::assertSame('grep', $this->builder->command());
    }

    public function testBuild(): void
    {
        $result = $this->builder
            ->lineNumber()
            ->noColor()
            ->fullName()
            ->context(3)
            ->pattern('test-pattern')
            ->hash('abc123')
            ->build();

        static::assertSame([
            'git',
            'grep',
            '-n',
            '--no-color',
            '--full-name',
            '--context 3',
            escapeshellarg('test-pattern'),
            'abc123'
        ], $result);
    }

    public function testBuildWithoutPatternAndHash(): void
    {
        $result = $this->builder
            ->lineNumber()
            ->noColor()
            ->build();

        static::assertSame([
            'git',
            'grep',
            '-n',
            '--no-color'
        ], $result);
    }

    public function testToString(): void
    {
        $result = (string)$this->builder
            ->pattern('search')
            ->hash('def456');

        static::assertSame("git grep " . escapeshellarg('search') . " def456", $result);
    }
}

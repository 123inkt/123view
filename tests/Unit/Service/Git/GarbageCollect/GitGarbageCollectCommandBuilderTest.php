<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\GarbageCollect;

use DR\Review\Service\Git\GarbageCollect\GitGarbageCollectCommandBuilder;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitGarbageCollectCommandBuilder::class)]
class GitGarbageCollectCommandBuilderTest extends AbstractTestCase
{
    private GitGarbageCollectCommandBuilder $builder;

    public function setUp(): void
    {
        parent::setUp();
        $this->builder = new GitGarbageCollectCommandBuilder('git');
    }

    public function testCommand(): void
    {
        static::assertSame('gc', $this->builder->command());
    }

    public function testBuild(): void
    {
        static::assertSame(['git', 'gc', '--aggressive', '--prune=now', '--quiet'], $this->builder->aggressive()->prune('now')->quiet()->build());
    }

    public function testToString(): void
    {
        static::assertSame('git gc --aggressive --prune=now --quiet', (string)$this->builder->aggressive()->prune('now')->quiet());
    }
}

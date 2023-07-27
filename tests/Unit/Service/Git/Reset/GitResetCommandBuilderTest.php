<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Reset;

use DR\Review\Service\Git\Reset\GitResetCommandBuilder;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitResetCommandBuilder::class)]
class GitResetCommandBuilderTest extends AbstractTestCase
{
    private GitResetCommandBuilder $builder;

    public function setUp(): void
    {
        parent::setUp();
        $this->builder = new GitResetCommandBuilder('git');
    }

    public function testBuild(): void
    {
        static::assertSame(['git', 'reset', '--hard', '--soft'], $this->builder->hard()->soft()->build());
    }
}

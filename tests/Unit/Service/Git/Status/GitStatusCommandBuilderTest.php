<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Status;

use DR\Review\Service\Git\Reset\GitResetCommandBuilder;
use DR\Review\Service\Git\Status\GitStatusCommandBuilder;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitResetCommandBuilder::class)]
class GitStatusCommandBuilderTest extends AbstractTestCase
{
    private GitStatusCommandBuilder $builder;

    public function setUp(): void
    {
        parent::setUp();
        $this->builder = new GitStatusCommandBuilder('git');
    }

    public function testBuild(): void
    {
        static::assertSame(['git', 'status', '--porcelain'], $this->builder->porcelain()->build());
    }
}

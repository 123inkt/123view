<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\RevList;

use DR\Review\Service\Git\RevList\GitRevListCommandBuilder;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitRevListCommandBuilder::class)]
class GitRevListCommandBuilderTest extends AbstractTestCase
{
    private GitRevListCommandBuilder $builder;

    public function setUp(): void
    {
        parent::setUp();
        $this->builder = new GitRevListCommandBuilder('git');
    }

    public function testCommand(): void
    {
        static::assertSame('rev-list', $this->builder->command());
    }

    public function testBuild(): void
    {
        static::assertSame(
            [
                'git',
                'rev-list',
                'start...end',
                '--left-only',
                '--right-only',
                '--left-right',
                '--pretty=format',
                '--no-merges'
            ],
            $this->builder->commitRange('start', 'end')
                ->leftOnly()
                ->rightOnly()
                ->leftRight()
                ->pretty('format')
                ->noMerges()
                ->build()
        );
    }

    public function testToString(): void
    {
        static::assertSame('git rev-list start...end --left-right', (string)$this->builder->commitRange('start', 'end')->leftRight());
    }
}

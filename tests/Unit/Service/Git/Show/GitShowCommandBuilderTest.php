<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Show;

use DR\Review\Service\Git\Show\GitShowCommandBuilder;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitShowCommandBuilder::class)]
class GitShowCommandBuilderTest extends AbstractTestCase
{
    private GitShowCommandBuilder $builder;

    public function setUp(): void
    {
        parent::setUp();
        $this->builder = new GitShowCommandBuilder('git');
    }

    public function testCommand(): void
    {
        static::assertSame('show', $this->builder->command());
    }

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
                escapeshellarg('hash:file'),
                '--ignore-space-at-eol',
                '--ignore-cr-at-eol',
                '--ignore-space-change',
                '--ignore-all-space',
                '| base64'
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
                ->base64encode()
                ->build()
        );
    }

    public function testToString(): void
    {
        static::assertSame(
            'git show foobar --unified=5 --no-patch --format="format" ' . escapeshellarg('hash:file') . ' --ignore-space-at-eol --ignore-cr-at-eol',
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

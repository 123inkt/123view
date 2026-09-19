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

    public function testRequiresShellFalseByDefault(): void
    {
        static::assertFalse($this->builder->requiresShell());
    }

    public function testRequiresShellTrueWithBase64(): void
    {
        static::assertTrue($this->builder->base64encode()->requiresShell());
    }

    /** Shell mode (base64): format is quoted, file is escapeshellarg'd, pipe token appended */
    public function testBuildShellMode(): void
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

    /** Argv mode (no base64): format has no extra quotes, file is raw */
    public function testBuildArgvMode(): void
    {
        static::assertSame(
            [
                'git',
                'show',
                'foobar',
                '--unified=5',
                '--no-patch',
                '--format=format',
                'hash:file',
                '--ignore-space-at-eol',
                '--ignore-cr-at-eol',
            ],
            $this->builder->startPoint('foobar')
                ->unified(5)
                ->noPatch()
                ->format('format')
                ->file('hash', 'file')
                ->ignoreSpaceAtEol()
                ->ignoreCrAtEol()
                ->build()
        );
    }

    public function testToString(): void
    {
        static::assertSame(
            'git show foobar --unified=5 --no-patch --format=format hash:file --ignore-space-at-eol --ignore-cr-at-eol',
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

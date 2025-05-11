<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Search\RipGrep\Command;

use DR\Review\Service\Search\RipGrep\Command\RipGrepCommandBuilder;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RipGrepCommandBuilder::class)]
class RipGrepCommandBuilderTest extends AbstractTestCase
{
    public function testBuild(): void
    {
        $builder = new RipGrepCommandBuilder();
        $builder->hidden()
            ->noColor()
            ->lineNumber()
            ->json()
            ->beforeContext(1)
            ->afterContext(2)
            ->glob('!.git/')
            ->glob('*.json')
            ->search('searchQuery');

        static::assertSame(
            implode(' ', [
                '/usr/bin/rg',
                escapeshellarg('--hidden'),
                escapeshellarg('--color=never'),
                escapeshellarg('--line-number'),
                escapeshellarg('--json'),
                escapeshellarg('--before-context=1'),
                escapeshellarg('--after-context=2'),
                escapeshellarg('--glob=!.git/'),
                escapeshellarg('--glob=*.json'),
                escapeshellarg('searchQuery')
            ]),
            $builder->build()
        );
    }
}

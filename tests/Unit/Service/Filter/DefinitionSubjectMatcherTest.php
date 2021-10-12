<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Filter;

use DR\GitCommitNotification\Entity\Config\Definition;
use DR\GitCommitNotification\Service\Filter\DefinitionSubjectMatcher;
use DR\GitCommitNotification\Tests\AbstractTest;
use RuntimeException;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Filter\DefinitionSubjectMatcher
 */
class DefinitionSubjectMatcherTest extends AbstractTest
{
    /**
     * @covers ::matches
     */
    public function testMatchesShouldFail(): void
    {
        $commit = $this->createCommit();

        $definition = new Definition();
        $definition->addSubject("/^BadRegex");

        $matcher = new DefinitionSubjectMatcher();
        $this->expectException(RuntimeException::class);
        $matcher->matches($commit, $definition);
    }

    /**
     * @covers ::matches
     */
    public function testMatchesShouldMatch(): void
    {
        $commit          = $this->createCommit();
        $commit->subject = "Foobar";

        $definition = new Definition();
        $definition->addSubject("/^Foo/");

        $matcher = new DefinitionSubjectMatcher();
        static::assertTrue($matcher->matches($commit, $definition));
    }

    /**
     * @covers ::matches
     */
    public function testMatchesShouldNotMatch(): void
    {
        $commit          = $this->createCommit();
        $commit->subject = "Barfoo";

        $definition = new Definition();
        $definition->addSubject("/^Foo/");

        $matcher = new DefinitionSubjectMatcher();
        static::assertFalse($matcher->matches($commit, $definition));
    }
}

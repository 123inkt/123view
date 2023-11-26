<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Filter;

use Doctrine\Common\Collections\ArrayCollection;
use DR\Review\Entity\Notification\Filter;
use DR\Review\Service\Filter\DefinitionSubjectMatcher;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use RuntimeException;

#[CoversClass(DefinitionSubjectMatcher::class)]
class DefinitionSubjectMatcherTest extends AbstractTestCase
{
    public function testMatchesShouldFail(): void
    {
        $commit = $this->createCommit();

        $filter = new Filter();
        $filter->setPattern("/^BadRegex");

        $matcher = new DefinitionSubjectMatcher();
        $this->expectException(RuntimeException::class);
        $matcher->matches($commit, new ArrayCollection([$filter]));
    }

    public function testMatchesShouldMatch(): void
    {
        $commit          = $this->createCommit();
        $commit->subject = "Foobar";

        $filter = new Filter();
        $filter->setPattern("/^Foo/");

        $matcher = new DefinitionSubjectMatcher();
        static::assertTrue($matcher->matches($commit, new ArrayCollection([$filter])));
    }

    public function testMatchesShouldNotMatch(): void
    {
        $commit          = $this->createCommit();
        $commit->subject = "Barfoo";

        $filter = new Filter();
        $filter->setPattern("/^Foo/");

        $matcher = new DefinitionSubjectMatcher();
        static::assertFalse($matcher->matches($commit, new ArrayCollection([$filter])));
    }
}

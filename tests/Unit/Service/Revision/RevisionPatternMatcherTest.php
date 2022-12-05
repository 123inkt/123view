<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Revision;

use DR\Review\Service\Revision\RevisionPatternMatcher;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Service\Revision\RevisionPatternMatcher
 * @covers ::__construct
 */
class RevisionPatternMatcherTest extends AbstractTestCase
{
    /**
     * @covers ::match
     */
    public function testMatch(): void
    {
        $matcher = new RevisionPatternMatcher('^F#\d+', 'bug');

        static::assertNull($matcher->match('foobar'));
        static::assertSame('F#123', $matcher->match('F#123 foobar'));
    }

    /**
     * @covers ::match
     */
    public function testMatchWithGroup(): void
    {
        $matcher = new RevisionPatternMatcher('^F#(?<bug>\d+)', 'bug');
        static::assertSame('123', $matcher->match('F#123 foobar'));
    }
}

<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Revision;

use DR\GitCommitNotification\Service\Revision\RevisionPatternMatcher;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Revision\RevisionPatternMatcher
 * @covers ::__construct
 */
class RevisionPatternMatcherTest extends AbstractTestCase
{
    /**
     * @covers ::match
     */
    public function testMatch(): void {

        $matcher = new RevisionPatternMatcher('^F#\d+');

        static::assertNull($matcher->match('foobar'));
        static::assertSame('F#123', $matcher->match('F#123 foobar'));
    }
}

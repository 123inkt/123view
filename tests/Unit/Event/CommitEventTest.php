<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Event;

use DR\GitCommitNotification\Event\CommitEvent;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Event\CommitEvent
 * @covers ::__construct
 */
class CommitEventTest extends AbstractTestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $commit = $this->createCommit();
        $event  = new CommitEvent($commit);
        static::assertSame($commit, $event->commit);
    }
}

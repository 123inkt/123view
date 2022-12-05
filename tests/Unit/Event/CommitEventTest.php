<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Event;

use DR\Review\Event\CommitEvent;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Event\CommitEvent
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

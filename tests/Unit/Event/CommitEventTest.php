<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Event;

use DR\Review\Event\CommitEvent;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommitEvent::class)]
class CommitEventTest extends AbstractTestCase
{
    public function testConstruct(): void
    {
        $commit = $this->createCommit();
        $event  = new CommitEvent($commit);
        static::assertSame($commit, $event->commit);
    }
}

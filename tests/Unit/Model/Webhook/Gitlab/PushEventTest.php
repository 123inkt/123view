<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Model\Webhook\Gitlab;

use DR\Review\Model\Webhook\Gitlab\PushEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PushEvent::class)]
class PushEventTest extends TestCase
{
    public function testToString(): void
    {
        $event            = new PushEvent();
        $event->objectKind = 'push';
        $event->eventType  = 'push';
        $event->projectId  = 123;

        static::assertSame('PushEvent(objectKind: push, eventType: push, projectId: 123)', (string)$event);
    }
}

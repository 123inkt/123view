<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Notification;

use DR\Review\Entity\Notification\Recipient;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionProperty;

#[CoversClass(Recipient::class)]
class RecipientTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(Recipient::class);
    }

    public function testId(): void
    {
        $recipient = new Recipient();
        static::assertFalse($recipient->hasId());

        (new ReflectionProperty(Recipient::class, 'id'))->setValue($recipient, 123);
        static::assertTrue($recipient->hasId());
        static::assertSame(123, $recipient->getId());
    }

    public function testClone(): void
    {
        clone new Recipient();
        static::expectNotToPerformAssertions();
    }
}

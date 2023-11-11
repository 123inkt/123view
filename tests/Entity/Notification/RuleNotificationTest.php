<?php
declare(strict_types=1);

namespace DR\Review\Tests\Entity\Notification;

use DR\Review\Entity\Notification\RuleNotification;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RuleNotification::class)]
class RuleNotificationTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(RuleNotification::class);
    }
}

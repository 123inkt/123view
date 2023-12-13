<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Notification;

use DR\Review\Doctrine\Type\NotificationSendType;
use DR\Review\Entity\Notification\RuleOptions;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RuleOptions::class)]
class RuleOptionsTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        static::assertNull((new RuleOptions())->getId());
        static::assertAccessorPairs(RuleOptions::class);
    }

    public function testSendType(): void
    {
        $options = new RuleOptions();
        static::assertTrue($options->hasSendType(NotificationSendType::MAIL));
        static::assertTrue($options->hasSendType(NotificationSendType::BROWSER));

        $options->setSendType(NotificationSendType::MAIL);
        static::assertTrue($options->hasSendType(NotificationSendType::MAIL));
        static::assertFalse($options->hasSendType(NotificationSendType::BROWSER));

        $options->setSendType(NotificationSendType::BROWSER);
        static::assertFalse($options->hasSendType(NotificationSendType::MAIL));
        static::assertTrue($options->hasSendType(NotificationSendType::BROWSER));
    }
}

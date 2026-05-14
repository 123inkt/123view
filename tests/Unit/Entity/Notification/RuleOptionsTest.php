<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Notification;

use DR\Review\Doctrine\Type\NotificationSendType;
use DR\Review\Entity\Notification\RuleOptions;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionProperty;

#[CoversClass(RuleOptions::class)]
class RuleOptionsTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(RuleOptions::class);
    }

    public function testId(): void
    {
        $options = new RuleOptions();
        static::assertFalse($options->hasId());

        (new ReflectionProperty(RuleOptions::class, 'id'))->setValue($options, 123);
        static::assertTrue($options->hasId());
        static::assertSame(123, $options->getId());
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

    public function testClone(): void
    {
        clone new RuleOptions();
        static::expectNotToPerformAssertions();
    }
}

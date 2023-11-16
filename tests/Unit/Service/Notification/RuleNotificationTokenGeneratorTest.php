<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Notification;

use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Notification\RuleNotification;
use DR\Review\Entity\User\User;
use DR\Review\Service\Notification\RuleNotificationTokenGenerator;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RuleNotificationTokenGenerator::class)]
class RuleNotificationTokenGeneratorTest extends AbstractTestCase
{
    private RuleNotificationTokenGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new RuleNotificationTokenGenerator('123view');
    }

    public function testGenerate(): void
    {
        $notification = new RuleNotification();
        $notification->setId(123);
        $notification->setNotifyTimestamp(123456789);
        $notification->setCreateTimestamp(987654321);
        $notification->setRule((new Rule())->setUser((new User())->setId(456)));

        $expected = hash('sha512', '123view123123view123456789987654321123view456123view');
        $result   = $this->generator->generate($notification);
        static::assertSame($expected, $result);
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\User;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use DR\Review\Entity\User\UserSetting;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserSetting::class)]
class UserSettingTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        $config = (new ConstraintConfig())->setExcludedMethods(['getBrowserNotificationEvents']);
        static::assertAccessorPairs(UserSetting::class, $config);
    }

    public function testBrowserNotificationEvents(): void
    {
        $setting = new UserSetting();
        static::assertFalse($setting->hasBrowserNotificationEvent('foobar'));

        $setting->setBrowserNotificationEvents(['foo', 'bar']);
        static::assertTrue($setting->hasBrowserNotificationEvent('foo'));

        static::assertSame(['foo', 'bar'], $setting->getBrowserNotificationEvents());
    }
}

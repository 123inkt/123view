<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\User;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use DR\Review\Entity\User\UserSetting;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Entity\User\UserSetting
 */
class UserSettingTest extends AbstractTestCase
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        $config = (new ConstraintConfig())->setExcludedMethods(['getBrowserNotificationEvents']);
        static::assertAccessorPairs(UserSetting::class, $config);
    }

    /**
     * @covers ::getBrowserNotificationEvents
     * @covers ::setBrowserNotificationEvents
     * @covers ::hasBrowserNotificationEvent
     */
    public function testBrowserNotificationEvents(): void
    {
        $setting = new UserSetting();
        static::assertFalse($setting->hasBrowserNotificationEvent('foobar'));

        $setting->setBrowserNotificationEvents(['foo', 'bar']);
        static::assertFalse($setting->hasBrowserNotificationEvent('foo'));

        static::assertSame(['foo', 'bar'], $setting->getBrowserNotificationEvents());
    }
}

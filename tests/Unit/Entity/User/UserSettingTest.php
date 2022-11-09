<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\User;

use DR\GitCommitNotification\Entity\User\UserSetting;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\User\UserSetting
 */
class UserSettingTest extends AbstractTestCase
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(UserSetting::class);
    }
}

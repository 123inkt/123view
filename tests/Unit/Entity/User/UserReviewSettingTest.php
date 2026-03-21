<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\User;

use DR\Review\Entity\User\UserReviewSetting;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserReviewSetting::class)]
class UserReviewSettingTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(UserReviewSetting::class);
    }
}

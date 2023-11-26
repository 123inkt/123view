<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\User;

use DR\Review\Entity\User\UserAccessToken;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserAccessToken::class)]
class UserAccessTokenTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(UserAccessToken::class);
    }
}

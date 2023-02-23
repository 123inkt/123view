<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\User;

use DR\Review\Entity\User\UserAccessToken;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Entity\User\UserAccessToken
 */
class UserAccessTokenTest extends AbstractTestCase
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(UserAccessToken::class);
    }
}
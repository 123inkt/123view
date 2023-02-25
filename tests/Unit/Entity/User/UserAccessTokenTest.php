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
     * @covers ::getId
     * @covers ::setId
     * @covers ::getToken
     * @covers ::setToken
     * @covers ::getName
     * @covers ::setName
     * @covers ::getUser
     * @covers ::setUser
     * @covers ::getUsages
     * @covers ::setUsages
     * @covers ::getCreateTimestamp
     * @covers ::setCreateTimestamp
     * @covers ::getUseTimestamp
     * @covers ::setUseTimestamp
     */
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(UserAccessToken::class);
    }
}

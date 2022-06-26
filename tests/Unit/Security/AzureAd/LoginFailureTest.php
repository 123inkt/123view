<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Security\AzureAd;

use DR\GitCommitNotification\Security\AzureAd\LoginFailure;
use DR\GitCommitNotification\Tests\AbstractTest;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Security\AzureAd\LoginFailure
 * @covers ::__construct
 */
class LoginFailureTest extends AbstractTest
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(LoginFailure::class);
    }

    /**
     * @covers ::isSuccess
     */
    public function testIsSuccess(): void
    {
        $success = new LoginFailure('message');
        static::assertFalse($success->isSuccess());
    }
}

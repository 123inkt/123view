<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Security\AzureAd;

use DR\GitCommitNotification\Security\AzureAd\LoginSuccess;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Security\AzureAd\LoginSuccess
 * @covers ::__construct
 */
class LoginSuccessTest extends AbstractTestCase
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(LoginSuccess::class);
    }

    /**
     * @covers ::isSuccess
     */
    public function testIsSuccess(): void
    {
        $success = new LoginSuccess('name', 'email');
        static::assertTrue($success->isSuccess());
    }
}

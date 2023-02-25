<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Security\AzureAd;

use DR\Review\Security\AzureAd\LoginSuccess;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Security\AzureAd\LoginSuccess
 * @covers ::__construct
 */
class LoginSuccessTest extends AbstractTestCase
{
    /**
     * @covers ::isSuccess
     * @covers ::getName
     * @covers ::getEmail
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

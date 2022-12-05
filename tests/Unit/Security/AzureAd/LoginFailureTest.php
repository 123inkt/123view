<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Security\AzureAd;

use DR\Review\Security\AzureAd\LoginFailure;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Security\AzureAd\LoginFailure
 * @covers ::__construct
 */
class LoginFailureTest extends AbstractTestCase
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

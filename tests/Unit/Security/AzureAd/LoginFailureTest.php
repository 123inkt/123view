<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Security\AzureAd;

use DR\Review\Security\AzureAd\LoginFailure;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LoginFailure::class)]
class LoginFailureTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(LoginFailure::class);
    }

    public function testIsSuccess(): void
    {
        $success = new LoginFailure('message');
        static::assertFalse($success->isSuccess());
    }
}

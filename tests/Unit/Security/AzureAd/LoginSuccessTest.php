<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Security\AzureAd;

use DR\Review\Security\AzureAd\LoginSuccess;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LoginSuccess::class)]
class LoginSuccessTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(LoginSuccess::class);
    }

    public function testIsSuccess(): void
    {
        $success = new LoginSuccess('name', 'email');
        static::assertTrue($success->isSuccess());
    }
}

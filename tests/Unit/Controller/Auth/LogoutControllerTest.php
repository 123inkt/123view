<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\Auth;

use DR\Review\Controller\Auth\LogoutController;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use RuntimeException;

#[CoversClass(LogoutController::class)]
class LogoutControllerTest extends AbstractTestCase
{
    public function testInvoke(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Logout security route is not configured');
        (new LogoutController())();
    }
}

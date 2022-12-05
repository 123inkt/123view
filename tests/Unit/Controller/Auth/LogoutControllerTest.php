<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\Auth;

use DR\Review\Controller\Auth\LogoutController;
use DR\Review\Tests\AbstractTestCase;
use RuntimeException;

/**
 * @coversDefaultClass \DR\Review\Controller\Auth\LogoutController
 */
class LogoutControllerTest extends AbstractTestCase
{
    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Logout security route is not configured');
        (new LogoutController())();
    }
}

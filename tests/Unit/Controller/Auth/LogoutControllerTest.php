<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\Auth;

use DR\GitCommitNotification\Controller\Auth\LogoutController;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use RuntimeException;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\Auth\LogoutController
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

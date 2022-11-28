<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\App\User;

use DR\GitCommitNotification\Controller\App\User\UserApprovalPendingController;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\App\User\UserApprovalPendingController
 * @covers ::__construct
 */
class UserApprovalPendingControllerTest extends AbstractTestCase
{
    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $controller = new UserApprovalPendingController();
        static::assertSame([], $controller());
    }
}

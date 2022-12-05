<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\User;

use DR\Review\Controller\App\User\UserApprovalPendingController;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Controller\App\User\UserApprovalPendingController
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

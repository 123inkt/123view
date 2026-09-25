<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\User;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Project\ProjectsController;
use DR\Review\Controller\App\User\UserApprovalPendingController;
use DR\Review\Security\Role\Roles;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends AbstractControllerTestCase<UserApprovalPendingController>
 */
#[CoversClass(UserApprovalPendingController::class)]
class UserApprovalPendingControllerTest extends AbstractControllerTestCase
{
    private Security&MockObject $security;

    public function setUp(): void
    {
        $this->security = $this->createMock(Security::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $this->security->expects($this->once())->method('isGranted')->with(Roles::ROLE_USER)->willReturn(false);
        static::assertSame([], ($this->controller)());
    }

    public function testInvokeUserAlreadyHasAccess(): void
    {
        $this->security->expects($this->once())->method('isGranted')->with(Roles::ROLE_USER)->willReturn(true);
        $this->expectRedirectToRoute(ProjectsController::class)->willReturn('url');
        ($this->controller)();
    }

    public function getController(): AbstractController
    {
        return new UserApprovalPendingController($this->security);
    }
}

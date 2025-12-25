<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\Auth;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Project\ProjectsController;
use DR\Review\Controller\App\User\UserApprovalPendingController;
use DR\Review\Controller\Auth\LoginController;
use DR\Review\Entity\User\User;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\Authentication\LoginViewModel;
use DR\Review\ViewModelProvider\LoginViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractControllerTestCase<LoginController>
 */
#[CoversClass(LoginController::class)]
class LoginControllerTest extends AbstractControllerTestCase
{
    private TranslatorInterface&MockObject    $translator;
    private UserEntityProvider&MockObject     $userEntityProvider;
    private AuthenticationUtils&MockObject    $authenticationUtils;
    private LoginViewModelProvider&MockObject $viewModelProvider;

    public function setUp(): void
    {
        $this->translator          = $this->createMock(TranslatorInterface::class);
        $this->userEntityProvider  = $this->createMock(UserEntityProvider::class);
        $this->authenticationUtils = $this->createMock(AuthenticationUtils::class);
        $this->viewModelProvider   = $this->createMock(LoginViewModelProvider::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $user = new User();
        $user->setRoles([Roles::ROLE_USER]);
        $request   = new Request();
        $viewModel = $this->createMock(LoginViewModel::class);

        $this->userEntityProvider->expects($this->once())->method('getUser')->willReturn(null);

        $this->authenticationUtils->expects($this->once())->method('getLastAuthenticationError')->willReturn(new AuthenticationException());
        $this->translator->expects($this->exactly(2))->method('trans')->willReturn('message', 'page_title');
        $this->expectAddFlash('error', 'message');
        $this->viewModelProvider->expects($this->once())->method('getLoginViewModel')->with($request)->willReturn($viewModel);

        $result = ($this->controller)($request);
        static::assertSame(['page_title' => 'page_title', 'loginModel' => $viewModel], $result);
    }

    public function testInvokeShouldRedirectUser(): void
    {
        $user = new User();
        $user->setRoles([Roles::ROLE_USER]);
        $request = new Request();

        $this->expectRedirectToRoute(ProjectsController::class)->willReturn('redirect-url');
        $this->userEntityProvider->expects($this->exactly(2))->method('getUser')->willReturn($user);
        $this->translator->expects($this->never())->method('trans');

        $result = ($this->controller)($request);
        static::assertInstanceOf(RedirectResponse::class, $result);
    }

    public function testInvokeShouldRedirectNewUser(): void
    {
        $user    = new User();
        $request = new Request();

        $this->expectRedirectToRoute(UserApprovalPendingController::class)->willReturn('redirect-url');
        $this->userEntityProvider->expects($this->exactly(2))->method('getUser')->willReturn($user);
        $this->translator->expects($this->never())->method('trans');

        $result = ($this->controller)($request);
        static::assertInstanceOf(RedirectResponse::class, $result);
    }

    public function getController(): AbstractController
    {
        return new LoginController($this->translator, $this->userEntityProvider, $this->authenticationUtils, $this->viewModelProvider);
    }
}

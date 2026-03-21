<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\User;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\User\UserAccessTokenController;
use DR\Review\Entity\User\User;
use DR\Review\Form\User\AddAccessTokenFormType;
use DR\Review\Service\User\UserAccessTokenIssuer;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\User\UserAccessTokenViewModel;
use DR\Review\ViewModelProvider\UserSettingViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends AbstractControllerTestCase<UserAccessTokenController>
 */
#[CoversClass(UserAccessTokenController::class)]
class UserAccessTokenControllerTest extends AbstractControllerTestCase
{
    private UserAccessTokenIssuer&MockObject        $accessTokenIssuer;
    private UserSettingViewModelProvider&MockObject $viewModelProvider;

    protected function setUp(): void
    {
        $this->accessTokenIssuer = $this->createMock(UserAccessTokenIssuer::class);
        $this->viewModelProvider = $this->createMock(UserSettingViewModelProvider::class);
        parent::setUp();
    }

    public function testInvokeNonSubmitted(): void
    {
        $request   = new Request();
        $viewModel = static::createStub(UserAccessTokenViewModel::class);

        $this->expectCreateForm(AddAccessTokenFormType::class)
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(false);
        $this->expectAddFlash('error', 'access.token.creation.failed');
        $this->viewModelProvider->expects($this->once())->method('getUserAccessTokenViewModel')->willReturn($viewModel);
        $this->accessTokenIssuer->expects($this->never())->method('issue');

        $result = ($this->controller)($request);
        static::assertSame(['accessTokenModel' => $viewModel], $result);
    }

    public function testInvokeSubmitted(): void
    {
        $request   = new Request();
        $user      = new User();
        $viewModel = static::createStub(UserAccessTokenViewModel::class);

        $this->expectGetUser($user);
        $this->expectCreateForm(AddAccessTokenFormType::class)
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(true)
            ->getDataWillReturn(['name' => 'name']);
        $this->expectAddFlash('success', 'access.token.creation.success');
        $this->accessTokenIssuer->expects($this->once())->method('issue')->with($user, 'name');
        $this->viewModelProvider->expects($this->once())->method('getUserAccessTokenViewModel')->willReturn($viewModel);

        $result = ($this->controller)($request);
        static::assertSame(['accessTokenModel' => $viewModel], $result);
    }

    public function getController(): AbstractController
    {
        return new UserAccessTokenController($this->accessTokenIssuer, $this->viewModelProvider);
    }
}

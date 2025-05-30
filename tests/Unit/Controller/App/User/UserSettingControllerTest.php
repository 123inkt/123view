<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\User;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\User\UserSettingController;
use DR\Review\Entity\User\User;
use DR\Review\Entity\User\UserSetting;
use DR\Review\Form\User\UserSettingFormType;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\User\UserSettingViewModel;
use DR\Review\ViewModelProvider\UserSettingViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends AbstractControllerTestCase<UserSettingController>
 */
#[CoversClass(UserSettingController::class)]
class UserSettingControllerTest extends AbstractControllerTestCase
{
    private UserSettingViewModelProvider&MockObject $provider;
    private UserRepository&MockObject               $userRepository;

    public function setUp(): void
    {
        $this->provider       = $this->createMock(UserSettingViewModelProvider::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        parent::setUp();
    }

    public function testInvokeIsNotSubmitted(): void
    {
        $request = new Request();
        $setting = new UserSetting();
        $user    = new User();
        $user->setSetting($setting);
        $viewModel = new UserSettingViewModel($this->createMock(FormView::class));

        $this->expectGetUser($user);
        $this->expectCreateForm(UserSettingFormType::class, ['setting' => $user->getSetting()])
            ->handleRequest($request)
            ->isSubmittedWillReturn(false);
        $this->provider->expects($this->once())->method('getUserSettingViewModel')->willReturn($viewModel);

        $this->userRepository->expects(self::never())->method('save');

        $result = ($this->controller)($request);
        static::assertEquals(['settingViewModel' => $viewModel], $result);
    }

    public function testInvokeIsSubmitted(): void
    {
        $request = new Request();
        $setting = new UserSetting();
        $user    = new User();
        $user->setSetting($setting);
        $viewModel = new UserSettingViewModel($this->createMock(FormView::class));

        $this->expectGetUser($user);
        $this->expectCreateForm(UserSettingFormType::class, ['setting' => $user->getSetting()])
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(true);
        $this->provider->expects($this->once())->method('getUserSettingViewModel')->willReturn($viewModel);

        $this->userRepository->expects($this->once())->method('save')->with($user, true);
        $this->expectAddFlash('success', 'settings.save.successfully');

        $result = ($this->controller)($request);
        static::assertEquals(['settingViewModel' => $viewModel], $result);
    }

    public function getController(): AbstractController
    {
        return new UserSettingController($this->userRepository, $this->provider);
    }
}

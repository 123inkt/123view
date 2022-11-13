<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\App\User;

use DR\GitCommitNotification\Controller\App\User\UserSettingController;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Entity\User\UserSetting;
use DR\GitCommitNotification\Form\User\UserSettingFormType;
use DR\GitCommitNotification\Repository\User\UserRepository;
use DR\GitCommitNotification\Tests\AbstractControllerTestCase;
use DR\GitCommitNotification\ViewModel\App\User\UserSettingViewModel;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\App\User\UserSettingController
 * @covers ::__construct
 */
class UserSettingControllerTest extends AbstractControllerTestCase
{
    private UserRepository&MockObject      $userRepository;
    private TranslatorInterface&MockObject $translator;

    public function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->translator     = $this->createMock(TranslatorInterface::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeIsNotSubmitted(): void
    {
        $request = new Request();
        $setting = new UserSetting();
        $user    = new User();
        $user->setSetting($setting);
        $formView = $this->createMock(FormView::class);

        $this->expectGetUser($user);
        $this->expectCreateForm(UserSettingFormType::class, ['setting' => $user->getSetting()])
            ->handleRequest($request)
            ->isSubmittedWillReturn(false)
            ->createViewWillReturn($formView);

        $this->userRepository->expects(self::never())->method('save');

        $result = ($this->controller)($request);
        static::assertEquals(['settingViewModel' => new UserSettingViewModel($formView)], $result);
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeIsSubmitted(): void
    {
        $request = new Request();
        $setting = new UserSetting();
        $user    = new User();
        $user->setSetting($setting);
        $formView = $this->createMock(FormView::class);

        $this->expectGetUser($user);
        $this->expectCreateForm(UserSettingFormType::class, ['setting' => $user->getSetting()])
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(true)
            ->createViewWillReturn($formView);

        $this->userRepository->expects(self::once())->method('save')->with($user, true);
        $this->translator->expects(self::once())->method('trans')->with('mail.settings.save.successfully')->willReturn('message');
        $this->expectAddFlash('success', 'message');

        $result = ($this->controller)($request);
        static::assertEquals(['settingViewModel' => new UserSettingViewModel($formView)], $result);
    }

    public function getController(): AbstractController
    {
        return new UserSettingController($this->userRepository, $this->translator);
    }
}

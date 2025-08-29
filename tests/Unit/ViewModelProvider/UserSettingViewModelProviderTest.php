<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use DR\Review\Entity\User\User;
use DR\Review\Entity\User\UserAccessToken;
use DR\Review\Repository\User\UserAccessTokenRepository;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModelProvider\UserSettingViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

#[CoversClass(UserSettingViewModelProvider::class)]
class UserSettingViewModelProviderTest extends AbstractTestCase
{
    private User                                 $user;
    private UserEntityProvider&MockObject       $userProvider;
    private UserAccessTokenRepository&MockObject $tokenRepository;
    private UserSettingViewModelProvider         $viewModelProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user              = new User();
        $this->userProvider      = $this->createMock(UserEntityProvider::class);
        $this->tokenRepository   = $this->createMock(UserAccessTokenRepository::class);
        $this->viewModelProvider = new UserSettingViewModelProvider($this->userProvider, $this->tokenRepository);
    }

    public function testGetUserSettingViewModel(): void
    {
        $formView = $this->createMock(FormView::class);
        $form     = $this->createMock(FormInterface::class);

        $form->expects($this->once())->method('createView')->willReturn($formView);

        $model = $this->viewModelProvider->getUserSettingViewModel($form);
        static::assertSame($formView, $model->settingForm);
    }

    public function testGetUserAccessTokenViewModel(): void
    {
        $formView    = $this->createMock(FormView::class);
        $form        = $this->createMock(FormInterface::class);
        $accessToken = new UserAccessToken();

        $form->expects($this->once())->method('createView')->willReturn($formView);
        $this->userProvider->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($this->user);
        $this->tokenRepository->expects($this->once())
            ->method('findBy')
            ->with(['user' => $this->user], ['createTimestamp' => 'DESC'])
            ->willReturn([$accessToken]);

        $model = $this->viewModelProvider->getUserAccessTokenViewModel($form);
        static::assertSame([$accessToken], $model->accessTokens);
        static::assertSame($formView, $model->addTokenForm);
    }
}

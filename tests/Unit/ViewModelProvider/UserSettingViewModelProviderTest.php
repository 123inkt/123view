<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use DR\Review\Entity\User\User;
use DR\Review\Entity\User\UserAccessToken;
use DR\Review\Repository\User\UserAccessTokenRepository;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModelProvider\UserSettingViewModelProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * @coversDefaultClass \DR\Review\ViewModelProvider\UserSettingViewModelProvider
 * @covers ::__construct
 */
class UserSettingViewModelProviderTest extends AbstractTestCase
{
    private User                                 $user;
    private UserAccessTokenRepository&MockObject $tokenRepository;
    private UserSettingViewModelProvider         $viewModelProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user              = new User();
        $this->tokenRepository   = $this->createMock(UserAccessTokenRepository::class);
        $this->viewModelProvider = new UserSettingViewModelProvider($this->user, $this->tokenRepository);
    }

    /**
     * @covers ::getUserSettingViewModel
     */
    public function testGetUserSettingViewModel(): void
    {
        $formView = $this->createMock(FormView::class);
        $form     = $this->createMock(FormInterface::class);

        $form->expects(self::once())->method('createView')->willReturn($formView);

        $model = $this->viewModelProvider->getUserSettingViewModel($form);
        static::assertSame($formView, $model->settingForm);
    }

    /**
     * @covers ::getUserAccessTokenViewModel
     */
    public function testGetUserAccessTokenViewModel(): void
    {
        $formView    = $this->createMock(FormView::class);
        $form        = $this->createMock(FormInterface::class);
        $accessToken = new UserAccessToken();

        $form->expects(self::once())->method('createView')->willReturn($formView);
        $this->tokenRepository->expects(self::once())
            ->method('findBy')
            ->with(['user' => $this->user], ['createTimestamp' => 'DESC'])
            ->willReturn([$accessToken]);

        $model = $this->viewModelProvider->getUserAccessTokenViewModel($form);
        static::assertSame([$accessToken], $model->accessTokens);
        static::assertSame($formView, $model->addTokenForm);
    }
}

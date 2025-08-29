<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Repository\User\UserAccessTokenRepository;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\ViewModel\App\User\UserAccessTokenViewModel;
use DR\Review\ViewModel\App\User\UserSettingViewModel;
use Symfony\Component\Form\FormInterface;

readonly class UserSettingViewModelProvider
{
    public function __construct(private UserEntityProvider $userProvider, private UserAccessTokenRepository $accessTokenRepository)
    {
    }

    public function getUserSettingViewModel(FormInterface $form): UserSettingViewModel
    {
        return new UserSettingViewModel($form->createView());
    }

    public function getUserAccessTokenViewModel(FormInterface $form): UserAccessTokenViewModel
    {
        $accessTokens = $this->accessTokenRepository->findBy(['user' => $this->userProvider->getCurrentUser()], ['createTimestamp' => 'DESC']);

        return new UserAccessTokenViewModel($accessTokens, $form->createView());
    }
}

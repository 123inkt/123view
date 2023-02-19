<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Entity\User\User;
use DR\Review\Form\User\AddAccessTokenFormType;
use DR\Review\Repository\User\UserAccessTokenRepository;
use DR\Review\ViewModel\App\User\UserSettingViewModel;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class UserSettingViewModelProvider
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly UserAccessTokenRepository $accessTokenRepository
    ) {
    }

    public function getUserSettingViewModel(User $user, FormInterface $form): UserSettingViewModel
    {
        $accessTokens = $this->accessTokenRepository->findBy(['user' => $user], ['createTimestamp' => 'DESC']);
        $addTokenForm = $this->formFactory->create(AddAccessTokenFormType::class);

        return new UserSettingViewModel($accessTokens, $addTokenForm->createView(), $form->createView());
    }
}

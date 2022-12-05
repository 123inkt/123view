<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Form\User\UserProfileFormType;
use DR\Review\Repository\User\UserRepository;
use DR\Review\ViewModel\App\User\UsersViewModel;
use Symfony\Component\Form\FormFactoryInterface;

class UserViewModelProvider
{
    public function __construct(private readonly FormFactoryInterface $formFactory, private readonly UserRepository $userRepository)
    {
    }

    public function getUsersViewModel(): UsersViewModel
    {
        $users = $this->userRepository->findBy([], ['name' => 'ASC']);
        $forms = [];

        foreach ($users as $user) {
            $forms[$user->getId()] = $this->formFactory->create(UserProfileFormType::class, $user, ['user' => $user])->createView();
        }

        return new UsersViewModel($users, $forms);
    }
}

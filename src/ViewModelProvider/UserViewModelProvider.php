<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Entity\User\User;
use DR\Review\Form\User\UserProfileFormType;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Security\Role\Roles;
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

        $sortedUsers = ['new' => [], 'approved' => [], 'banned' => []];

        foreach ($users as $user) {
            if (in_array(Roles::ROLE_BANNED, $user->getRoles(), true)) {
                $sortedUsers['banned'][] = $user;
            } elseif (in_array(Roles::ROLE_USER, $user->getRoles(), true)) {
                $sortedUsers['approved'][] = $user;
            } else {
                $sortedUsers['new'][] = $user;
            }

            $forms[$user->getId()] = $this->formFactory->create(UserProfileFormType::class, $user, ['user' => $user])->createView();
        }

        /** @var User[] $users */
        $users = array_merge(...array_values($sortedUsers));

        return new UsersViewModel($users, $forms);
    }
}

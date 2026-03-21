<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Admin;

use DR\Review\Controller\AbstractController;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\App\User\UsersViewModel;
use DR\Review\ViewModelProvider\UserViewModelProvider;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UsersController extends AbstractController
{
    public function __construct(private readonly UserViewModelProvider $viewModelProvider)
    {
    }

    /**
     * @return array<string, UsersViewModel>
     */
    #[Route('/app/admin/users', self::class, methods: 'GET')]
    #[Template('app/admin/users.html.twig')]
    #[IsGranted(Roles::ROLE_ADMIN)]
    public function __invoke(): array
    {
        return ['usersViewModel' => $this->viewModelProvider->getUsersViewModel()];
    }
}

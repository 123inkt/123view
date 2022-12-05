<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\User;

use DR\Review\Controller\AbstractController;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\App\User\UsersViewModel;
use DR\Review\ViewModelProvider\UserViewModelProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

class UsersController extends AbstractController
{
    public function __construct(private readonly UserViewModelProvider $viewModelProvider)
    {
    }

    /**
     * @return array<string, UsersViewModel>
     */
    #[Route('/app/users', self::class, methods: 'GET')]
    #[Template('app/user/users.html.twig')]
    #[IsGranted(Roles::ROLE_ADMIN)]
    public function __invoke(): array
    {
        return ['usersViewModel' => $this->viewModelProvider->getUsersViewModel()];
    }
}

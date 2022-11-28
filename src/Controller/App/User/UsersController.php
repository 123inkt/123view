<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\User;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Security\Role\Roles;
use DR\GitCommitNotification\ViewModelProvider\UserViewModelProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UsersController extends AbstractController
{
    public function __construct(private readonly UserViewModelProvider $viewModelProvider)
    {
    }

    /**
     * @return array<string, User[]>
     */
    #[Route('/app/users', self::class, methods: 'GET')]
    #[Template('app/user/users.html.twig')]
    #[IsGranted(Roles::ROLE_ADMIN)]
    public function __invoke(Request $request): array
    {
        return ['usersViewModel' => $this->viewModelProvider->getUsersViewModel()];
    }
}

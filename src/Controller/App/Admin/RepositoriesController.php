<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Admin;

use DR\Review\Controller\AbstractController;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\App\Admin\RepositoriesViewModel;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RepositoriesController extends AbstractController
{
    public function __construct(private readonly RepositoryRepository $repositoryRepository)
    {
    }

    /**
     * @return array<string, RepositoriesViewModel>
     */
    #[Route('/app/admin/repositories', self::class, methods: 'GET')]
    #[Template('app/admin/repositories.html.twig')]
    #[IsGranted(Roles::ROLE_ADMIN)]
    public function __invoke(): array
    {
        $repositories = $this->repositoryRepository->findBy([], ['displayName' => 'ASC']);

        return ['repositoriesViewModel' => new RepositoriesViewModel($repositories)];
    }
}

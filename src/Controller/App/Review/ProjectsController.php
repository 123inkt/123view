<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review;

use DR\Review\Controller\AbstractController;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\App\Review\ProjectsViewModel;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjectsController extends AbstractController
{
    public function __construct(private readonly RepositoryRepository $repositoryRepository, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * @return array<string, string|ProjectsViewModel>
     */
    #[Route('app/projects', name: self::class, methods: 'GET')]
    #[Template('app/review/projects.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(): array
    {
        $favorites    = $this->repositoryRepository->findBy(['active' => 1, 'favorite' => 1], ['name' => 'ASC']);
        $repositories = $this->repositoryRepository->findBy(['active' => 1, 'favorite' => 0], ['name' => 'ASC']);

        return [
            'page_title'    => $this->translator->trans('projects'),
            'projectsModel' => new ProjectsViewModel($favorites, $repositories)
        ];
    }
}

<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Repository\Config\RepositoryRepository;
use DR\GitCommitNotification\ViewModel\App\Review\ProjectsViewModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
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
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function __invoke(): array
    {
        $repositories = $this->repositoryRepository->findBy(['active' => 1], ['name' => 'ASC']);

        return [
            'page_title'    => $this->translator->trans('projects'),
            'projectsModel' => new ProjectsViewModel($repositories)
        ];
    }
}

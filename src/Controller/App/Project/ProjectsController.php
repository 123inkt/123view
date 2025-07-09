<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Project;

use Doctrine\DBAL\Exception;
use DR\Review\Controller\AbstractController;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\App\Project\ProjectsViewModel;
use DR\Review\ViewModelProvider\ProjectsViewModelProvider;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjectsController extends AbstractController
{
    public function __construct(private readonly ProjectsViewModelProvider $viewModelProvider, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * @return array<string, string|ProjectsViewModel>
     * @throws Exception
     */
    #[Route('app/projects', name: self::class, methods: 'GET')]
    #[Template('app/project/projects.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request): array
    {
        return [
            'page_title' => $this->translator->trans('projects'),
            'projectsModel' => $this->viewModelProvider->getProjectsViewModel(trim($request->query->get('search', '')))
        ];
    }

    /**
     * @throws Exception
     */
    #[Route('app/projects.json', name: self::class . 'api')]
    #[IsGranted(Roles::ROLE_USER)]
    public function api(Request $request): Response
    {
        $viewModel = $this->viewModelProvider->getProjectsViewModel(trim($request->query->get('search', '')));

        return $this->json($viewModel, context: [
            'groups' => [
                'app:projects',
                'app:timeline',
                'repository:read',
                'comment:read',
                'comment-reply:read'
            ]
        ]);
    }
}

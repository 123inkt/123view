<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Project;

use Doctrine\DBAL\Exception;
use DR\Review\Controller\AbstractController;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\Api\Anthropic\AnthropicCodeReview;
use DR\Review\ViewModel\App\Project\ProjectsViewModel;
use DR\Review\ViewModelProvider\ProjectsViewModelProvider;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjectsController extends AbstractController
{
    public function __construct(
        private readonly ProjectsViewModelProvider $viewModelProvider,
        private readonly TranslatorInterface $translator,
        private readonly AnthropicCodeReview $codeReview,
        private readonly CodeReviewRepository $reviewRepository,
    ) {
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
        $review    = $this->reviewRepository->find(24588);
        $this->codeReview->requestCodeReview($review);

        return [
            'page_title'    => $this->translator->trans('projects'),
            'projectsModel' => $this->viewModelProvider->getProjectsViewModel(trim($request->query->get('search', '')))
        ];
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Revision;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Model\Page\Breadcrumb;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\Page\BreadcrumbFactory;
use DR\Review\ViewModelProvider\RevisionViewModelProvider;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RevisionsController extends AbstractController
{
    public function __construct(
        private readonly BreadcrumbFactory $breadcrumbFactory,
        private readonly RevisionViewModelProvider $revisionViewModelProvider
    ) {
    }

    /**
     * @return array<string, object|Breadcrumb[]>
     */
    #[Route('app/projects/{id<\d+>}/revisions', name: self::class, methods: 'GET')]
    #[Template('app/revision/revisions.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request, #[MapEntity] Repository $repository): array
    {
        $searchQuery = trim($request->query->get('search', ''));
        $page        = $request->query->getInt('page', 1);

        return [
            'breadcrumbs'    => $this->breadcrumbFactory->createForReviews($repository),
            'revisionsModel' => $this->revisionViewModelProvider->getRevisionsViewModel($repository, $page, $searchQuery)
        ];
    }
}

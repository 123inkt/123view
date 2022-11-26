<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Revision;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Entity\Repository\Repository;
use DR\GitCommitNotification\Model\Page\Breadcrumb;
use DR\GitCommitNotification\Service\Page\BreadcrumbFactory;
use DR\GitCommitNotification\ViewModelProvider\RevisionViewModelProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('repository')]
    public function __invoke(Request $request, Repository $repository): array
    {
        $searchQuery = trim($request->query->get('search', ''));
        $page        = $request->query->getInt('page', 1);

        return [
            'breadcrumbs'    => $this->breadcrumbFactory->createForReviews($repository),
            'revisionsModel' => $this->revisionViewModelProvider->getRevisionsViewModel($repository, $page, $searchQuery)
        ];
    }
}

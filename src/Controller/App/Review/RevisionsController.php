<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Model\Page\Breadcrumb;
use DR\GitCommitNotification\Repository\Config\ExternalLinkRepository;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;
use DR\GitCommitNotification\Service\Page\BreadcrumbFactory;
use DR\GitCommitNotification\ViewModel\App\Review\PaginatorViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\RevisionsViewModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RevisionsController extends AbstractController
{
    public function __construct(
        private readonly RevisionRepository $revisionRepository,
        private readonly ExternalLinkRepository $externalLinkRepository,
        private readonly BreadcrumbFactory $breadcrumbFactory
    ) {
    }

    /**
     * @return array<string, object|Breadcrumb[]>
     */
    #[Route('app/projects/{id<\d+>}/revisions', name: self::class, methods: 'GET')]
    #[Template('app/review/revisions.html.twig')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('repository')]
    public function __invoke(Request $request, Repository $repository): array
    {
        $searchQuery   = trim($request->query->get('search', ''));
        $page          = $request->query->getInt('page', 1);
        $paginator     = $this->revisionRepository->getPaginatorForSearchQuery((int)$repository->getId(), $page, $searchQuery);
        $externalLinks = $this->externalLinkRepository->findAll();

        /** @var PaginatorViewModel<Revision> $paginatorViewModel */
        $paginatorViewModel = new PaginatorViewModel($paginator, $page);

        return [
            'breadcrumbs'    => $this->breadcrumbFactory->createForReviews($repository),
            'revisionsModel' => new RevisionsViewModel($repository, $paginator, $paginatorViewModel, $externalLinks, $searchQuery)
        ];
    }
}

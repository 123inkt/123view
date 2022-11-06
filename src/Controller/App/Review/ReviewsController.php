<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Model\Page\Breadcrumb;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Service\Page\BreadcrumbFactory;
use DR\GitCommitNotification\ViewModel\App\Review\PaginatorViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\ReviewsViewModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ReviewsController extends AbstractController
{
    public function __construct(private readonly CodeReviewRepository $reviewRepository, private readonly BreadcrumbFactory $breadcrumbFactory)
    {
    }

    /**
     * @return array<string, string|object|Breadcrumb[]>
     */
    #[Route('app/projects/{id<\d+>}/reviews', name: self::class, methods: 'GET')]
    #[Template('app/review/reviews.html.twig')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('repository')]
    public function __invoke(Request $request, Repository $repository): array
    {
        $searchQuery = trim($request->query->get('search', 'state:open'));
        $page        = $request->query->getInt('page', 1);
        $paginator   = $this->reviewRepository->getPaginatorForSearchQuery($this->getUser(), (int)$repository->getId(), $page, $searchQuery);

        /** @var PaginatorViewModel<CodeReview> $paginatorViewModel */
        $paginatorViewModel = new PaginatorViewModel($paginator, $page);

        return [
            'page_title'   => ucfirst((string)$repository->getName()),
            'breadcrumbs'  => $this->breadcrumbFactory->createForReviews($repository),
            'reviewsModel' => new ReviewsViewModel($repository, $paginator, $paginatorViewModel, $searchQuery)
        ];
    }
}

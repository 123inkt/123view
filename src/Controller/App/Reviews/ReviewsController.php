<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Reviews;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Model\Page\Breadcrumb;
use DR\Review\Repository\Review\CodeReviewQueryBuilder;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\Page\BreadcrumbFactory;
use DR\Review\ViewModel\App\Review\PaginatorViewModel;
use DR\Review\ViewModel\App\Review\ReviewsViewModel;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ReviewsController extends AbstractController
{
    public function __construct(private readonly CodeReviewRepository $reviewRepository, private readonly BreadcrumbFactory $breadcrumbFactory)
    {
    }

    /**
     * @return array<string, string|object|Breadcrumb[]>
     */
    #[Route('app/projects/{id<\d+>}/reviews', name: self::class, methods: 'GET')]
    #[Template('app/reviews/reviews.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request, #[MapEntity] Repository $repository): array
    {
        $searchQuery   = trim($request->query->get('search', 'state:open '));
        $searchOrderBy = trim($request->query->get('order-by', CodeReviewQueryBuilder::ORDER_UPDATE_TIMESTAMP));
        $page          = $request->query->getInt('page', 1);
        $paginator     = $this->reviewRepository->getPaginatorForSearchQuery(
            $this->getUser(),
            (int)$repository->getId(),
            $page,
            $searchQuery,
            $searchOrderBy
        );

        /** @var PaginatorViewModel<CodeReview> $paginatorViewModel */
        $paginatorViewModel = new PaginatorViewModel($paginator, $page);

        return [
            'page_title'   => ucfirst((string)$repository->getDisplayName()),
            'breadcrumbs'  => $this->breadcrumbFactory->createForReviews($repository),
            'reviewsModel' => new ReviewsViewModel($repository, $paginator, $paginatorViewModel, $searchQuery, $searchOrderBy)
        ];
    }
}

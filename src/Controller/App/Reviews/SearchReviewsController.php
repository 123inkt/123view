<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Reviews;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Repository\Review\CodeReviewQueryBuilder;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\App\Review\PaginatorViewModel;
use DR\Review\ViewModel\App\Review\ReviewsViewModel;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SearchReviewsController extends AbstractController
{
    public function __construct(private readonly CodeReviewRepository $reviewRepository)
    {
    }

    /**
     * @return array<string, string|object>
     */
    #[Route('app/search', name: self::class, methods: 'GET')]
    #[Template('app/reviews/reviews.search.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request): array
    {
        $searchQuery   = trim($request->query->get('search', ''));
        $searchOrderBy = trim($request->query->get('order-by', CodeReviewQueryBuilder::ORDER_UPDATE_TIMESTAMP));
        $page          = $request->query->getInt('page', 1);
        $paginator     = $this->reviewRepository->getPaginatorForSearchQuery($this->getUser(), null, $page, $searchQuery, $searchOrderBy);

        /** @var PaginatorViewModel<CodeReview> $paginatorViewModel */
        $paginatorViewModel = new PaginatorViewModel($paginator, $page);

        return [
            'reviewsModel' => new ReviewsViewModel(null, $paginator, $paginatorViewModel, $searchQuery, $searchOrderBy)
        ];
    }
}

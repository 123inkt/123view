<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Reviews;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\QueryParser\ParserHasFailedFormatter;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Request\Reviews\SearchReviewsRequest;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\App\Review\PaginatorViewModel;
use DR\Review\ViewModel\App\Review\ReviewsViewModel;
use Parsica\Parsica\ParserHasFailed;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SearchReviewsController extends AbstractController
{
    public function __construct(
        private readonly CodeReviewRepository $reviewRepository,
        private readonly ParserHasFailedFormatter $parseErrorFormatter
    ) {
    }

    /**
     * @return array<string, string|object>
     */
    #[Route('app/search', name: self::class, methods: 'GET')]
    #[Template('app/reviews/reviews.search.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(SearchReviewsRequest $request): array
    {
        $paginator = null;
        $paginatorViewModel = null;

        try {
            $paginator = $this->reviewRepository->getPaginatorForSearchQuery(
                null,
                $request->getPage(),
                $request->getSearchQuery(),
                $request->getOrderBy()
            );
            /** @var PaginatorViewModel<CodeReview> $paginatorViewModel */
            $paginatorViewModel = new PaginatorViewModel($paginator, $request->getPage());
        } catch (ParserHasFailed $error) {
            $this->addFlash('error', $this->parseErrorFormatter->format($error));
        }

        return [
            'reviewsModel' => new ReviewsViewModel(null, $paginator, $paginatorViewModel, $request->getSearchQuery(), $request->getOrderBy(), null)
        ];
    }
}

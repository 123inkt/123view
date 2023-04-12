<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Reviews;

use DR\Review\Controller\AbstractController;
use DR\Review\QueryParser\ParserHasFailedFormatter;
use DR\Review\Request\Reviews\SearchReviewsRequest;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\CodeReview\Search\ReviewSearchQueryTermFactory;
use DR\Review\ViewModelProvider\ReviewsViewModelProvider;
use Exception;
use Parsica\Parsica\ParserHasFailed;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SearchReviewsController extends AbstractController
{
    public function __construct(
        private readonly ReviewsViewModelProvider $viewModelProvider,
        private readonly ReviewSearchQueryTermFactory $termFactory,
        private readonly ParserHasFailedFormatter $parseFailFormatter
    ) {
    }

    /**
     * @return array<string, string|object>
     * @throws Exception
     */
    #[Route('app/search', name: self::class, methods: 'GET')]
    #[Template('app/reviews/reviews.search.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(SearchReviewsRequest $request): array
    {
        try {
            $terms = $this->termFactory->getSearchTerms($request->getSearchQuery());
        } catch (ParserHasFailed $error) {
            $this->addFlash('error', $this->parseFailFormatter->format($error));
            $terms = null;
        }

        return ['reviewsModel' => $this->viewModelProvider->getSearchReviewsViewModel($request, $terms)];
    }
}

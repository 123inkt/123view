<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Reviews;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Model\Page\Breadcrumb;
use DR\Review\QueryParser\InvalidQueryException;
use DR\Review\QueryParser\ParserHasFailedFormatter;
use DR\Review\Request\Reviews\SearchReviewsRequest;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\CodeReview\Search\ReviewSearchQueryTermFactory;
use DR\Review\Service\Page\BreadcrumbFactory;
use DR\Review\ViewModelProvider\ReviewsViewModelProvider;
use Exception;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ReviewsController extends AbstractController
{
    public function __construct(
        private readonly ReviewsViewModelProvider $viewModelProvider,
        private readonly BreadcrumbFactory $breadcrumbFactory,
        private readonly ReviewSearchQueryTermFactory $termFactory,
        private readonly ParserHasFailedFormatter $parseFailFormatter
    ) {
    }

    /**
     * @return array<string, string|object|Breadcrumb[]>
     * @throws Exception
     */
    #[Route('app/projects/{id<\d+>}/reviews', name: self::class, methods: 'GET')]
    #[Template('app/reviews/reviews.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(SearchReviewsRequest $request, #[MapEntity] Repository $repository): array
    {
        try {
            $terms = $this->termFactory->getSearchTerms($request->getSearchQuery());
        } catch (InvalidQueryException $error) {
            $this->addFlash('error', $this->parseFailFormatter->format($error));
            $terms = null;
        }

        return [
            'page_title'   => ucfirst($repository->getDisplayName()),
            'breadcrumbs'  => $this->breadcrumbFactory->createForReviews($repository),
            'reviewsModel' => $this->viewModelProvider->getReviewsViewModel($request, $terms, $repository)
        ];
    }
}

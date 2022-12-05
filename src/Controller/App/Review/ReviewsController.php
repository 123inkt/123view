<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Model\Page\Breadcrumb;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\Page\BreadcrumbFactory;
use DR\Review\ViewModel\App\Review\PaginatorViewModel;
use DR\Review\ViewModel\App\Review\ReviewsViewModel;
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
    #[IsGranted(Roles::ROLE_USER)]
    #[Entity('repository')]
    public function __invoke(Request $request, Repository $repository): array
    {
        $searchQuery = trim($request->query->get('search', 'state:open '));
        $page        = $request->query->getInt('page', 1);
        $paginator   = $this->reviewRepository->getPaginatorForSearchQuery($this->getUser(), (int)$repository->getId(), $page, $searchQuery);

        /** @var PaginatorViewModel<CodeReview> $paginatorViewModel */
        $paginatorViewModel = new PaginatorViewModel($paginator, $page);

        return [
            'page_title'   => ucfirst((string)$repository->getDisplayName()),
            'breadcrumbs'  => $this->breadcrumbFactory->createForReviews($repository),
            'reviewsModel' => new ReviewsViewModel($repository, $paginator, $paginatorViewModel, $searchQuery)
        ];
    }
}

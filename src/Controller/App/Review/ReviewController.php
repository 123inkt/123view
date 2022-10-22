<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\LineReference;
use DR\GitCommitNotification\Model\Page\Breadcrumb;
use DR\GitCommitNotification\Service\CodeReview\CodeReviewActionFactory;
use DR\GitCommitNotification\Service\Page\BreadcrumbFactory;
use DR\GitCommitNotification\ViewModelProvider\ReviewViewModelProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class ReviewController extends AbstractController
{
    public function __construct(
        private readonly ReviewViewModelProvider $modelProvider,
        private readonly BreadcrumbFactory $breadcrumbFactory,
        private readonly CodeReviewActionFactory $actionFactory
    ) {
    }

    /**
     * @return array<string, object|Breadcrumb[]>
     * @throws Throwable
     */
    #[Route('app/reviews/{id<\d+>}', name: self::class, methods: 'GET')]
    #[Template('app/review/review.html.twig')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('review')]
    public function __invoke(Request $request, CodeReview $review): array
    {
        $filePath = $request->query->get('filePath');
        $action   = $this->actionFactory->createFromRequest($request);

        return [
            'breadcrumbs' => $this->breadcrumbFactory->createForReview($review),
            'reviewModel' => $this->modelProvider->getViewModel($review, $filePath, $action)
        ];
    }
}

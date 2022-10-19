<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Model\Page\Breadcrumb;
use DR\GitCommitNotification\ViewModelProvider\ReviewViewModelProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class ReviewController extends AbstractController
{
    public function __construct(private readonly ReviewViewModelProvider $modelProvider)
    {
    }

    /**
     * @return array<string, object>
     * @throws Throwable
     */
    #[Route('app/reviews/{id<\d+>}', name: self::class, methods: 'GET')]
    #[Template('app/review/review.html.twig')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('review')]
    public function __invoke(Request $request, CodeReview $review): array
    {
        $filePath      = $request->query->get('filePath');
        $lineReference = $request->query->get('addComment');
        $breadcrumbs = [
            new Breadcrumb($review->getRepository()?->getName(), $this->generateUrl(ReviewsController::class, ['id' => $review->getRepository()?->getId()])),
            new Breadcrumb('CR-' . $review->getId(), $this->generateUrl(self::class, ['id' => $review->getId()]))
        ];

        return [
            'breadcrumbs' => $breadcrumbs,
            'reviewModel' => $this->modelProvider->getViewModel($review, $filePath, $lineReference)
        ];
    }
}

<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Model\Page\Breadcrumb;
use DR\GitCommitNotification\Request\Review\ReviewRequest;
use DR\GitCommitNotification\Service\CodeReview\FileSeenStatusService;
use DR\GitCommitNotification\Service\Page\BreadcrumbFactory;
use DR\GitCommitNotification\ViewModelProvider\ReviewViewModelProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class ReviewController extends AbstractController
{
    public function __construct(
        private readonly ReviewViewModelProvider $modelProvider,
        private readonly BreadcrumbFactory $breadcrumbFactory,
        private readonly FileSeenStatusService $fileSeenService
    ) {
    }

    /**
     * @return array<string, string|object|Breadcrumb[]>
     * @throws Throwable
     */
    #[Route('app/reviews/{id<\d+>}', name: self::class, methods: 'GET')]
    #[Template('app/review/review.html.twig')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('review')]
    public function __invoke(ReviewRequest $request, CodeReview $review): array
    {
        $viewModel = $this->modelProvider->getViewModel($review, $request->getFilePath(), $request->getTab(), $request->getAction());

        $this->fileSeenService->markAsSeen($review, $this->getUser(), $viewModel->fileDiffViewModel->getSelectedFile());

        return [
            'page_title'  => 'CR-' . $review->getProjectId() . ' - ' . ucfirst((string)$review->getRepository()?->getDisplayName()),
            'breadcrumbs' => $this->breadcrumbFactory->createForReview($review),
            'reviewModel' => $viewModel
        ];
    }
}

<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Model\Page\Breadcrumb;
use DR\GitCommitNotification\Service\CodeReview\CodeReviewActionFactory;
use DR\GitCommitNotification\Service\CodeReview\FileSeenStatusService;
use DR\GitCommitNotification\Service\Page\BreadcrumbFactory;
use DR\GitCommitNotification\ViewModel\App\Review\ReviewViewModel;
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
        private readonly CodeReviewActionFactory $actionFactory,
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
    public function __invoke(Request $request, CodeReview $review): array
    {
        $filePath = $request->query->get('filePath');
        $tab      = $request->query->get('tab', ReviewViewModel::SIDEBAR_TAB_OVERVIEW);
        $action   = $this->actionFactory->createFromRequest($request);

        $viewModel = $this->modelProvider->getViewModel($review, $filePath, $tab, $action);

        $selectedFile = $viewModel->getFileDiffViewModel()->getSelectedFile();
        $this->fileSeenService->markAsSeen($review, $this->getUser(), $selectedFile);

        return [
            'page_title'  => 'CR-' . $review->getProjectId() . ' - ' . ucfirst((string)$review->getRepository()?->getDisplayName()),
            'breadcrumbs' => $this->breadcrumbFactory->createForReview($review),
            'reviewModel' => $viewModel
        ];
    }
}

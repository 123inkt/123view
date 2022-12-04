<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Model\Page\Breadcrumb;
use DR\GitCommitNotification\Request\Review\ReviewRequest;
use DR\GitCommitNotification\Security\Role\Roles;
use DR\GitCommitNotification\Service\CodeReview\FileSeenStatusService;
use DR\GitCommitNotification\Service\Page\BreadcrumbFactory;
use DR\GitCommitNotification\ViewModelProvider\ReviewViewModelProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('app/reviews/{id<\d+>}', name: self::class . 'deprecated', methods: 'GET')]
    #[IsGranted(Roles::ROLE_USER)]
    #[Entity('review')]
    public function redirectReviewRoute(Request $request, CodeReview $review): RedirectResponse
    {
        return $this->redirectToRoute(self::class, ['review' => $review] + $request->query->all());
    }

    /**
     * @return array<string, string|object|Breadcrumb[]>
     * @throws Throwable
     */
    #[Route('app/{repositoryName<[\w-]+>}/review/cr-{reviewId<\d+>}', name: self::class, methods: 'GET')]
    #[Template('app/review/review.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    #[Entity('review', expr: 'repository.findByUrl(repositoryName, reviewId)')]
    public function __invoke(ReviewRequest $request, CodeReview $review): array
    {
        $viewModel = $this->modelProvider->getViewModel($review, $request->getFilePath(), $request->getTab(), $request->getAction());

        $this->fileSeenService->markAsSeen($review, $this->getUser(), $viewModel->getFileDiffViewModel()?->selectedFile);

        return [
            'page_title'  => 'CR-' . $review->getProjectId() . ' - ' . ucfirst((string)$review->getRepository()?->getDisplayName()),
            'breadcrumbs' => $this->breadcrumbFactory->createForReview($review),
            'reviewModel' => $viewModel
        ];
    }
}

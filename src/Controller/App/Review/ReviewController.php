<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Model\Page\Breadcrumb;
use DR\Review\Request\Review\ReviewRequest;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\CodeReview\FileSeenStatusService;
use DR\Review\Service\Page\BreadcrumbFactory;
use DR\Review\ViewModelProvider\ReviewViewModelProvider;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
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
    public function redirectReviewRoute(Request $request, #[MapEntity] CodeReview $review): RedirectResponse
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
    public function __invoke(ReviewRequest $request, #[MapEntity(expr: 'repository.findByUrl(repositoryName, reviewId)')] CodeReview $review): array
    {
        $viewModel = $this->modelProvider->getViewModel($review, $request);

        $this->fileSeenService->markAsSeen($review, $this->getUser(), $viewModel->getFileDiffViewModel()?->selectedFile);

        return [
            'page_title'  => 'CR-' . $review->getProjectId() . ' - ' . ucfirst($review->getRepository()->getDisplayName()),
            'breadcrumbs' => $this->breadcrumbFactory->createForReview($review),
            'reviewModel' => $viewModel
        ];
    }
}

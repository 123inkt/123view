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
use Symfony\Bridge\Twig\Attribute\Entity;
use Symfony\Bridge\Twig\Attribute\IsGranted;
use Symfony\Bridge\Twig\Attribute\Template;
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

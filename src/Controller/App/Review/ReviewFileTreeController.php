<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\CodeReview\CodeReviewFileService;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Review\ReviewSessionService;
use DR\Review\ViewModel\App\Review\FileTreeViewModel;
use DR\Review\ViewModelProvider\FileTreeViewModelProvider;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

class ReviewFileTreeController extends AbstractController
{
    public function __construct(
        private readonly FileTreeViewModelProvider $viewModelProvider,
        private readonly CodeReviewFileService $fileService,
        private readonly ReviewSessionService $sessionService,
        private readonly CodeReviewRevisionService $revisionService,
    ) {
    }

    /**
     * @return array<string, FileTreeViewModel>
     * @throws Throwable
     */
    #[Route('app/reviews/{id<\d+>}/file-tree', name: self::class, methods: 'GET')]
    #[IsGranted(Roles::ROLE_USER)]
    #[Template('app/review/review.file_tree.html.twig')]
    public function __invoke(Request $request, #[MapEntity] CodeReview $review): array
    {
        // get diff files for review
        [$fileTree, $selectedFile] = $this->fileService->getFiles(
            $review,
            $this->revisionService->getRevisions($review),
            $request->query->get('filePath'),
            new FileDiffOptions(FileDiffOptions::DEFAULT_LINE_DIFF, $this->sessionService->getDiffComparePolicyForUser())
        );

        return ['fileTreeModel' => $this->viewModelProvider->getFileTreeViewModel($review, $fileTree, $selectedFile)];
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Request\Review\FileReviewRequest;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\CodeReview\FileSeenStatusService;
use DR\Review\Service\CodeReview\UserReviewSettingsProvider;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use DR\Review\ViewModelProvider\FileReviewViewModelProvider;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

class GetFileReviewController extends AbstractController
{
    public function __construct(
        private readonly FileReviewViewModelProvider $modelProvider,
        private readonly FileSeenStatusService $fileSeenService,
        private readonly UserReviewSettingsProvider $settingsProvider
    ) {
    }

    /**
     * @throws Throwable
     */
    #[Route('app/reviews/{id<\d+>}/file-review', name: self::class, methods: 'GET')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(FileReviewRequest $request, #[MapEntity] CodeReview $review): Response
    {
        $viewModel = $this->modelProvider->getViewModel(
            $review,
            $request->getFilePath(),
            $this->settingsProvider->getComparisonPolicy(),
            $this->settingsProvider->getReviewDiffMode(),
            $this->settingsProvider->getVisibleLines()
        );

        $this->fileSeenService->markAsSeen($review, $this->getUser(), $viewModel->selectedFile);

        $template = 'app/review/commit/commit.file.html.twig';
        if ($viewModel->selectedFile->isModified() && $this->settingsProvider->getReviewDiffMode() === ReviewDiffModeEnum::SIDE_BY_SIDE) {
            $template = 'app/review/commit/side-by-side/commit.file.html.twig';
        }

        return $this->render($template, ['fileDiffViewModel' => $viewModel]);
    }
}

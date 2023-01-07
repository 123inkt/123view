<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Form\Review\AddReviewerFormType;
use DR\Review\Request\Review\ReviewRequest;
use DR\Review\Service\CodeReview\CodeReviewFileService;
use DR\Review\Service\Revision\RevisionVisibilityProvider;
use DR\Review\ViewModel\App\Review\ReviewViewModel;
use Symfony\Component\Form\FormFactoryInterface;
use Throwable;

class ReviewViewModelProvider
{
    public function __construct(
        private readonly FileDiffViewModelProvider $fileDiffViewModelProvider,
        private readonly FormFactoryInterface $formFactory,
        private readonly CodeReviewFileService $fileService,
        private readonly FileTreeViewModelProvider $fileTreeModelProvider,
        private readonly RevisionViewModelProvider $revisionModelProvider,
        private readonly ReviewTimelineViewModelProvider $timelineViewModelProvider,
        private readonly RevisionVisibilityProvider $visibilityProvider,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function getViewModel(CodeReview $review, ReviewRequest $request): ReviewViewModel
    {
        $viewModel = new ReviewViewModel($review);
        $revisions = $review->getRevisions()->toArray();

        // visible revisions
        $visibleRevisions = $this->visibilityProvider->getVisibleRevisions($review, $revisions);
        $viewModel->setVisibleRevisionCount(count($visibleRevisions));

        // get diff files for review
        [$fileTree, $selectedFile] = $this->fileService->getFiles($review, $visibleRevisions, $request->getFilePath());

        // get timeline or file-diff view model
        if ($selectedFile === null) {
            $viewModel->setTimelineViewModel($this->timelineViewModelProvider->getTimelineViewModel($review));
            $viewModel->setDescriptionVisible(true);
        } else {
            $viewModel->setFileDiffViewModel(
                $this->fileDiffViewModelProvider->getFileDiffViewModel($review, $selectedFile, $request->getAction(), $request->getDiffMode())
            );
            $viewModel->setDescriptionVisible(false);
        }

        // get sidebar view model
        $viewModel->setSidebarTabMode($request->getTab());
        if ($request->getTab() === ReviewViewModel::SIDEBAR_TAB_OVERVIEW) {
            $viewModel->setAddReviewerForm($this->formFactory->create(AddReviewerFormType::class, null, ['review' => $review])->createView());
            $viewModel->setFileTreeModel($this->fileTreeModelProvider->getFileTreeViewModel($review, $fileTree, $selectedFile));
        }
        if ($request->getTab() === ReviewViewModel::SIDEBAR_TAB_REVISIONS) {
            $viewModel->setRevisionViewModel($this->revisionModelProvider->getRevisionViewModel($review, $revisions));
        }

        return $viewModel;
    }
}

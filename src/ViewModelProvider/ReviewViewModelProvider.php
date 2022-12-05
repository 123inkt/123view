<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Form\Review\AddReviewerFormType;
use DR\Review\Model\Review\Action\AbstractReviewAction;
use DR\Review\Service\CodeReview\DiffFinder;
use DR\Review\Service\CodeReview\FileTreeGenerator;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\Review\Utility\Assert;
use DR\Review\ViewModel\App\Review\ReviewViewModel;
use Symfony\Component\Form\FormFactoryInterface;
use Throwable;

class ReviewViewModelProvider
{
    public function __construct(
        private readonly FileDiffViewModelProvider $fileDiffViewModelProvider,
        private readonly ReviewDiffServiceInterface $diffService,
        private readonly FormFactoryInterface $formFactory,
        private readonly FileTreeGenerator $treeGenerator,
        private readonly FileTreeViewModelProvider $fileTreeModelProvider,
        private readonly RevisionViewModelProvider $revisionModelProvider,
        private readonly ReviewTimelineViewModelProvider $timelineViewModelProvider,
        private readonly DiffFinder $diffFinder
    ) {
    }

    /**
     * @throws Throwable
     */
    public function getViewModel(CodeReview $review, ?string $filePath, string $sidebarTab, ?AbstractReviewAction $reviewAction): ReviewViewModel
    {
        $viewModel = new ReviewViewModel($review);
        $revisions = $review->getRevisions()->toArray();

        // gather diff files
        $files    = $this->diffService->getDiffFiles(Assert::notNull($review->getRepository()), $revisions, new FileDiffOptions(9999999));
        $fileTree = $this->treeGenerator->generate($files)
            ->flatten()
            ->sort(static fn(DiffFile $left, DiffFile $right) => strcmp($left->getFilename(), $right->getFilename()));

        // get selected file (if any)
        $selectedFile = $this->diffFinder->findFileByPath($files, $filePath);

        // get timeline or file-diff view model
        if ($selectedFile === null) {
            $viewModel->setTimelineViewModel($this->timelineViewModelProvider->getTimelineViewModel($review));
            $viewModel->setDescriptionVisible(true);
        } else {
            $viewModel->setFileDiffViewModel($this->fileDiffViewModelProvider->getFileDiffViewModel($review, $selectedFile, $reviewAction));
            $viewModel->setDescriptionVisible(false);
        }

        // get sidebar view model
        $viewModel->setSidebarTabMode($sidebarTab);
        if ($sidebarTab === ReviewViewModel::SIDEBAR_TAB_OVERVIEW) {
            $viewModel->setAddReviewerForm($this->formFactory->create(AddReviewerFormType::class, null, ['review' => $review])->createView());
            $viewModel->setFileTreeModel($this->fileTreeModelProvider->getFileTreeViewModel($review, $fileTree, $selectedFile));
        }
        if ($sidebarTab === ReviewViewModel::SIDEBAR_TAB_REVISIONS) {
            $viewModel->setRevisionViewModel($this->revisionModelProvider->getRevisionViewModel($review, $revisions));
        }

        return $viewModel;
    }
}

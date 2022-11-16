<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModelProvider;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Form\Review\AddReviewerFormType;
use DR\GitCommitNotification\Form\Review\DetachRevisionsFormType;
use DR\GitCommitNotification\Model\Review\Action\AbstractReviewAction;
use DR\GitCommitNotification\Model\Review\DirectoryTreeNode;
use DR\GitCommitNotification\Service\CodeReview\DiffFinder;
use DR\GitCommitNotification\Service\CodeReview\FileSeenStatusService;
use DR\GitCommitNotification\Service\CodeReview\FileTreeGenerator;
use DR\GitCommitNotification\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\GitCommitNotification\Service\Git\Show\GitShowService;
use DR\GitCommitNotification\Utility\Assert;
use DR\GitCommitNotification\ViewModel\App\Review\FileTreeViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\ReviewRevisionViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\ReviewViewModel;
use Highlight\Highlighter;
use Symfony\Component\Form\FormFactoryInterface;
use Throwable;

class ReviewViewModelProvider
{
    public function __construct(
        private readonly FileDiffViewModelProvider $fileDiffViewModelProvider,
        private readonly ReviewDiffServiceInterface $diffService,
        private readonly FormFactoryInterface $formFactory,
        private readonly FileTreeGenerator $treeGenerator,
        private readonly FileSeenStatusService $fileStatusService,
        private readonly DiffFinder $diffFinder,
        private readonly GitShowService $showService
    ) {
    }

    /**
     * @throws Throwable
     */
    public function getViewModel(CodeReview $review, ?string $filePath, string $sidebarTab, ?AbstractReviewAction $reviewAction): ReviewViewModel
    {
        $revisions = $review->getRevisions()->toArray();
        $files     = $this->diffService->getDiffFiles(Assert::notNull($review->getRepository()), $revisions);
        $fileTree  = $this->treeGenerator->generate($files)
            ->flatten()
            ->sort(static fn(DiffFile $left, DiffFile $right) => strcmp($left->getFilename(), $right->getFilename()));

        // find selected file
        $selectedFile = $this->diffFinder->findFileByPath($files, $filePath);
        if ($selectedFile === null && count($files) > 0) {
            $selectedFile = $fileTree->getFirstFileInTree();
        }

        $viewModel = new ReviewViewModel(
            $review,
            $this->fileDiffViewModelProvider->getFileDiffViewModel($review, $selectedFile, $reviewAction)
        );
        $viewModel->setDescriptionVisible($filePath === null);

        $filePath = $selectedFile?->filePathAfter ?? $selectedFile?->filePathBefore ?? null;
        $revision = $review->getRevisions()->last();
        if ($filePath !== null && $revision instanceof Revision) {
            $output    = $this->showService->getFileAtRevision($revision, $filePath);
            $highlight = new Highlighter();
            $output    = $highlight->highlight('typescript', $output)->value;
            $viewModel->setHighlightedFile($output);
        }

        $viewModel->setSidebarTabMode($sidebarTab);
        if ($sidebarTab === ReviewViewModel::SIDEBAR_TAB_OVERVIEW) {
            $viewModel->setAddReviewerForm($this->formFactory->create(AddReviewerFormType::class, null, ['review' => $review])->createView());
            $viewModel->setFileTreeModel($this->getFileTreeViewModel($review, $fileTree, $selectedFile));
        }
        if ($sidebarTab === ReviewViewModel::SIDEBAR_TAB_REVISIONS) {
            $viewModel->setRevisionViewModel($this->getRevisionViewModel($review, $revisions));
        }

        return $viewModel;
    }

    /**
     * @param Revision[] $revisions
     */
    public function getRevisionViewModel(CodeReview $review, array $revisions): ReviewRevisionViewModel
    {
        return new ReviewRevisionViewModel(
            $revisions,
            $this->formFactory->create(DetachRevisionsFormType::class, null, ['reviewId' => $review->getId(), 'revisions' => $revisions])->createView(
            )
        );
    }

    /**
     * @param DirectoryTreeNode<DiffFile> $treeNode
     */
    public function getFileTreeViewModel(CodeReview $review, DirectoryTreeNode $treeNode, ?DiffFile $selectedFile): FileTreeViewModel
    {
        return new FileTreeViewModel(
            $review,
            $treeNode,
            $review->getComments(),
            $this->fileStatusService->getFileSeenStatus($review),
            $selectedFile
        );
    }
}

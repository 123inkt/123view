<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModelProvider;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Form\Review\AddReviewerFormType;
use DR\GitCommitNotification\Form\Review\DetachRevisionsForm;
use DR\GitCommitNotification\Model\Review\Action\AbstractReviewAction;
use DR\GitCommitNotification\Repository\Config\ExternalLinkRepository;
use DR\GitCommitNotification\Service\CodeReview\DiffFinder;
use DR\GitCommitNotification\Service\CodeReview\FileTreeGenerator;
use DR\GitCommitNotification\Service\Git\Review\ReviewDiffService;
use DR\GitCommitNotification\Utility\Type;
use DR\GitCommitNotification\ViewModel\App\Review\FileTreeViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\ReviewRevisionViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\ReviewViewModel;
use Symfony\Component\Form\FormFactoryInterface;
use Throwable;

class ReviewViewModelProvider
{
    public function __construct(
        private readonly ExternalLinkRepository $linkRepository,
        private readonly FileDiffViewModelProvider $fileDiffViewModelProvider,
        private readonly ReviewDiffService $diffService,
        private readonly FormFactoryInterface $formFactory,
        private readonly FileTreeGenerator $treeGenerator,
        private readonly DiffFinder $diffFinder
    ) {
    }

    /**
     * @throws Throwable
     */
    public function getViewModel(CodeReview $review, ?string $filePath, string $sidebarTab, ?AbstractReviewAction $reviewAction): ReviewViewModel
    {
        $revisions = $review->getRevisions()->toArray();
        $files     = $this->diffService->getDiffFiles($revisions);

        // find selected file
        $selectedFile = $this->diffFinder->findFileByPath($files, $filePath);
        if ($selectedFile === null && count($files) > 0) {
            $selectedFile = Type::notFalse(reset($files));
        }

        $viewModel = new ReviewViewModel(
            $review,
            $this->fileDiffViewModelProvider->getFileDiffViewModel($review, $selectedFile, $reviewAction),
            $this->linkRepository->findAll()
        );

        $viewModel->setSidebarTabMode($sidebarTab);
        if ($sidebarTab === ReviewViewModel::SIDEBAR_TAB_OVERVIEW) {
            $viewModel->setAddReviewerForm($this->formFactory->create(AddReviewerFormType::class, null, ['review' => $review])->createView());
            $viewModel->setFileTreeModel($this->getFileTreeViewModel($review, $files, $selectedFile));
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
            $this->formFactory->create(DetachRevisionsForm::class, null, ['reviewId' => $review->getId(), 'revisions' => $revisions])->createView()
        );
    }

    /**
     * @param DiffFile[] $files
     */
    public function getFileTreeViewModel(CodeReview $review, array $files, ?DiffFile $selectedFile): FileTreeViewModel
    {
        return new FileTreeViewModel(
            $this->treeGenerator->generate($files)
                ->flatten()
                ->sort(static fn(DiffFile $left, DiffFile $right) => strcmp($left->getFilename(), $right->getFilename())),
            $review->getComments(),
            $selectedFile
        );
    }
}

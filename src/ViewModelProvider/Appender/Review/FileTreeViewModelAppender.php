<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider\Appender\Review;

use DR\Review\Form\Review\AddReviewerFormType;
use DR\Review\Model\Review\ReviewDto;
use DR\Review\ViewModel\App\Review\ReviewViewModel;
use DR\Review\ViewModelProvider\FileTreeViewModelProvider;
use Symfony\Component\Form\FormFactoryInterface;

readonly class FileTreeViewModelAppender implements ReviewViewModelAppenderInterface
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private FileTreeViewModelProvider $fileTreeModelProvider,
    ) {
    }

    public function accepts(ReviewDto $dto, ReviewViewModel $viewModel): bool
    {
        return $viewModel->getSidebarTabMode() === ReviewViewModel::SIDEBAR_TAB_OVERVIEW;
    }

    public function append(ReviewDto $dto, ReviewViewModel $viewModel): void
    {
        $review       = $dto->review;
        $fileTree     = $dto->fileTree;
        $selectedFile = $dto->selectedFile;

        $viewModel->setAddReviewerForm($this->formFactory->create(AddReviewerFormType::class, null, ['review' => $review])->createView());
        $viewModel->setFileTreeModel($this->fileTreeModelProvider->getFileTreeViewModel($review, $fileTree, $selectedFile));
    }
}

<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModelProvider;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Form\Review\AddReviewerFormType;
use DR\GitCommitNotification\Model\Review\Action\AbstractReviewAction;
use DR\GitCommitNotification\Repository\Config\ExternalLinkRepository;
use DR\GitCommitNotification\Service\CodeReview\DiffFinder;
use DR\GitCommitNotification\Service\CodeReview\FileTreeGenerator;
use DR\GitCommitNotification\Service\Git\GitCodeReviewDiffService;
use DR\GitCommitNotification\Utility\Type;
use DR\GitCommitNotification\ViewModel\App\Review\FileTreeViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\ReviewViewModel;
use Symfony\Component\Form\FormFactoryInterface;
use Throwable;

class ReviewViewModelProvider
{
    public function __construct(
        private readonly ExternalLinkRepository $linkRepository,
        private readonly FileDiffViewModelProvider $fileDiffViewModelProvider,
        private readonly GitCodeReviewDiffService $diffService,
        private readonly FormFactoryInterface $formFactory,
        private readonly FileTreeGenerator $treeGenerator,
        private readonly DiffFinder $diffFinder
    ) {
    }

    /**
     * @throws Throwable
     */
    public function getViewModel(CodeReview $review, ?string $filePath, ?AbstractReviewAction $reviewAction): ReviewViewModel
    {
        $files = $this->diffService->getDiffFiles($review->getRevisions()->toArray());

        // find selected file
        $selectedFile = $this->diffFinder->findFileByPath($files, $filePath);
        if ($selectedFile === null && count($files) > 0) {
            $selectedFile = Type::notFalse(reset($files));
        }

        return new ReviewViewModel(
            $review,
            $this->getFileTreeViewModel($review, $files),
            $this->fileDiffViewModelProvider->getFileDiffViewModel($review, $selectedFile, $reviewAction),
            $this->formFactory->create(AddReviewerFormType::class, null, ['review' => $review])->createView(),
            $this->linkRepository->findAll()
        );
    }

    /**
     * @param DiffFile[] $files
     */
    public function getFileTreeViewModel(CodeReview $review, array $files): FileTreeViewModel
    {
        return new FileTreeViewModel(
            $this->treeGenerator->generate($files)->flatten(),
            $review->getComments()
        );
    }
}

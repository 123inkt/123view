<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModelProvider;

use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Form\Review\AddReviewerFormType;
use DR\GitCommitNotification\Repository\Config\ExternalLinkRepository;
use DR\GitCommitNotification\Service\CodeReview\FileTreeGenerator;
use DR\GitCommitNotification\Service\Git\GitCodeReviewDiffService;
use DR\GitCommitNotification\Utility\Type;
use DR\GitCommitNotification\ViewModel\App\Review\ReviewViewModel;
use Symfony\Component\Form\FormFactoryInterface;
use Throwable;

class ReviewViewModelProvider
{
    public function __construct(
        private readonly ExternalLinkRepository $linkRepository,
        private readonly GitCodeReviewDiffService $diffService,
        private readonly FormFactoryInterface $formFactory,
        private readonly FileTreeGenerator $treeGenerator
    ) {
    }

    /**
     * @throws Throwable
     */
    public function getViewModel(CodeReview $review, ?string $filePath): ReviewViewModel
    {
        $files = $this->diffService->getDiffFiles($review->getRevisions()->toArray());

        // find selected file
        $selectedFile = Type::notFalse(reset($files));
        if ($filePath !== null) {
            foreach ($files as $file) {
                if ($file->getFile()?->getPathname() === $filePath) {
                    $selectedFile = $file;
                }
            }
        }

        return new ReviewViewModel(
            $review,
            $this->treeGenerator->generate($files)->flatten(),
            $selectedFile,
            $this->formFactory->create(AddReviewerFormType::class, null, ['review' => $review])->createView(),
            $this->linkRepository->findAll()
        );
    }
}

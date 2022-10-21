<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModelProvider;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\LineReference;
use DR\GitCommitNotification\Form\Review\AddCommentFormType;
use DR\GitCommitNotification\Form\Review\AddCommentReplyFormType;
use DR\GitCommitNotification\Form\Review\AddReviewerFormType;
use DR\GitCommitNotification\Repository\Config\ExternalLinkRepository;
use DR\GitCommitNotification\Repository\Review\CommentRepository;
use DR\GitCommitNotification\Service\CodeReview\DiffFinder;
use DR\GitCommitNotification\Service\CodeReview\FileTreeGenerator;
use DR\GitCommitNotification\Service\Git\GitCodeReviewDiffService;
use DR\GitCommitNotification\Utility\Type;
use DR\GitCommitNotification\ViewModel\App\Review\AddCommentViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\CommentsViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\ReplyCommentViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\ReviewViewModel;
use Symfony\Component\Form\FormFactoryInterface;
use Throwable;

class ReviewViewModelProvider
{
    public function __construct(
        private readonly ExternalLinkRepository $linkRepository,
        private readonly CommentRepository $commentRepository,
        private readonly GitCodeReviewDiffService $diffService,
        private readonly FormFactoryInterface $formFactory,
        private readonly FileTreeGenerator $treeGenerator,
        private readonly DiffFinder $diffFinder
    ) {
    }

    /**
     * @throws Throwable
     */
    public function getViewModel(CodeReview $review, ?string $filePath, ?LineReference $lineReference, int $replyToComment): ReviewViewModel
    {
        $files = $this->diffService->getDiffFiles($review->getRevisions()->toArray());

        // find selected file
        $selectedFile = $this->diffFinder->findFileByPath($files, $filePath) ?? Type::notFalse(reset($files));

        $viewModel = new ReviewViewModel(
            $review,
            $this->treeGenerator->generate($files)->flatten(),
            $selectedFile,
            $this->formFactory->create(AddReviewerFormType::class, null, ['review' => $review])->createView(),
            $this->linkRepository->findAll()
        );

        if ($selectedFile !== null && $lineReference !== null) {
            $viewModel->setAddCommentForm($this->getAddCommentViewModel($review, $selectedFile, $lineReference));
        }

        if ($selectedFile !== null) {
            $viewModel->setCommentsViewModel($this->getCommentsViewModel($review, $selectedFile));
        }

        if ($replyToComment > 0) {
            $viewModel->setReplyCommentForm($this->getReplyCommentViewModel($replyToComment));
        }

        return $viewModel;
    }

    public function getAddCommentViewModel(CodeReview $review, DiffFile $file, LineReference $lineReference): AddCommentViewModel
    {
        $line = $this->diffFinder->findLineInFile($file, $lineReference);
        $form = $this->formFactory->create(AddCommentFormType::class, null, ['review' => $review, 'lineReference' => $lineReference])->createView();

        return new AddCommentViewModel($form, $line);
    }

    public function getReplyCommentViewModel(int $replyToComment): ReplyCommentViewModel
    {
        $comment = $this->commentRepository->find($replyToComment);
        $form    = $this->formFactory->create(AddCommentReplyFormType::class, null, ['comment' => $comment])->createView();

        return new ReplyCommentViewModel($form, $comment);
    }

    public function getCommentsViewModel(CodeReview $review, DiffFile $file): CommentsViewModel
    {
        $comments = $this->commentRepository->findByReview($review, (string)($file->filePathBefore ?? $file->filePathAfter));

        $diffLines       = [];
        $groupedComments = [];
        foreach ($comments as $comment) {
            $lineReference = (string)$comment->getLineReference();

            $groupedComments[$lineReference][] = $comment;
            if (isset($diffLines[$lineReference]) === false) {
                $line = $this->diffFinder->findLineInFile($file, $comment->getLineReference());
                if ($line !== null) {
                    $diffLines[spl_object_hash($line)] = $lineReference;
                }
            }
        }

        return new CommentsViewModel($groupedComments, $diffLines);
    }
}

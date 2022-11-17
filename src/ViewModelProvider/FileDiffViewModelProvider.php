<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModelProvider;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Form\Review\AddCommentFormType;
use DR\GitCommitNotification\Form\Review\AddCommentReplyFormType;
use DR\GitCommitNotification\Form\Review\EditCommentFormType;
use DR\GitCommitNotification\Form\Review\EditCommentReplyFormType;
use DR\GitCommitNotification\Model\Review\Action\AbstractReviewAction;
use DR\GitCommitNotification\Model\Review\Action\AddCommentAction;
use DR\GitCommitNotification\Model\Review\Action\AddCommentReplyAction;
use DR\GitCommitNotification\Model\Review\Action\EditCommentAction;
use DR\GitCommitNotification\Model\Review\Action\EditCommentReplyAction;
use DR\GitCommitNotification\Repository\Review\CommentRepository;
use DR\GitCommitNotification\Service\CodeHighlight\CacheableHighlightedFileService;
use DR\GitCommitNotification\Service\CodeReview\DiffFinder;
use DR\GitCommitNotification\Utility\Assert;
use DR\GitCommitNotification\ViewModel\App\Review\AddCommentViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\CommentsViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\EditCommentReplyViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\EditCommentViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\FileDiffViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\ReplyCommentViewModel;
use Symfony\Component\Form\FormFactoryInterface;
use Throwable;

/**
 * @suppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FileDiffViewModelProvider
{
    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly FormFactoryInterface $formFactory,
        private readonly DiffFinder $diffFinder,
        private readonly CacheableHighlightedFileService $highlightedFileService
    ) {
    }

    /**
     * @throws Throwable
     */
    public function getFileDiffViewModel(CodeReview $review, ?DiffFile $selectedFile, ?AbstractReviewAction $reviewAction): FileDiffViewModel
    {
        $viewModel = new FileDiffViewModel($selectedFile);

        if ($selectedFile !== null) {
            $viewModel->setCommentsViewModel($this->getCommentsViewModel($review, $selectedFile));

            $highlightedFile = $this->highlightedFileService->getHighlightedFile(
                Assert::notFalse($review->getRevisions()->last()),
                $selectedFile->getPathname()
            );
            $viewModel->setHighlightedFile($highlightedFile);
        }

        // setup action forms
        if ($selectedFile !== null && $reviewAction instanceof AddCommentAction) {
            $viewModel->setAddCommentForm($this->getAddCommentViewModel($review, $selectedFile, $reviewAction));
        } elseif ($reviewAction instanceof EditCommentAction) {
            $viewModel->setEditCommentForm($this->getEditCommentViewModel($reviewAction));
        } elseif ($reviewAction instanceof AddCommentReplyAction) {
            $viewModel->setReplyCommentForm($this->getReplyCommentViewModel($reviewAction));
        } elseif ($reviewAction instanceof EditCommentReplyAction) {
            $viewModel->setEditReplyCommentForm($this->getEditCommentReplyViewModel($reviewAction));
        }

        return $viewModel;
    }

    public function getAddCommentViewModel(CodeReview $review, DiffFile $file, AddCommentAction $action): AddCommentViewModel
    {
        $lineReference = $action->lineReference;
        $line          = Assert::notNull($this->diffFinder->findLineInFile($file, $lineReference));

        $form = $this->formFactory->create(AddCommentFormType::class, null, ['review' => $review, 'lineReference' => $lineReference])->createView();

        return new AddCommentViewModel($form, $line);
    }

    public function getEditCommentViewModel(EditCommentAction $action): ?EditCommentViewModel
    {
        $comment = $action->comment;
        if ($comment === null) {
            return null;
        }
        $form = $this->formFactory->create(EditCommentFormType::class, $comment, ['comment' => $comment])->createView();

        return new EditCommentViewModel($form, $comment);
    }

    public function getReplyCommentViewModel(AddCommentReplyAction $action): ?ReplyCommentViewModel
    {
        $comment = $action->comment;
        if ($comment === null) {
            return null;
        }
        $form = $this->formFactory->create(AddCommentReplyFormType::class, null, ['comment' => $comment])->createView();

        return new ReplyCommentViewModel($form, $comment);
    }

    public function getEditCommentReplyViewModel(EditCommentReplyAction $action): ?EditCommentReplyViewModel
    {
        $reply = $action->reply;
        if ($reply === null) {
            return null;
        }
        $form = $this->formFactory->create(EditCommentReplyFormType::class, $reply, ['reply' => $reply])->createView();

        return new EditCommentReplyViewModel($form, $reply);
    }

    public function getCommentsViewModel(CodeReview $review, DiffFile $file): CommentsViewModel
    {
        $comments = $this->commentRepository->findByReview($review, (string)($file->filePathBefore ?? $file->filePathAfter));

        $diffLines        = [];
        $detachedComments = [];
        $groupedComments  = [];
        foreach ($comments as $comment) {
            $lineReference = (string)$comment->getLineReference();

            $groupedComments[$lineReference][] = $comment;
            if (isset($diffLines[$lineReference]) !== false) {
                $detachedComments[] = $comment;
                continue;
            }

            $line = $this->diffFinder->findLineInFile($file, Assert::notNull($comment->getLineReference()));
            if ($line !== null) {
                $diffLines[spl_object_hash($line)] = $lineReference;
            } else {
                $detachedComments[] = $comment;
            }
        }

        return new CommentsViewModel($groupedComments, $detachedComments, $diffLines);
    }
}

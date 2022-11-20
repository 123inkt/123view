<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModelProvider;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Form\Review\AddCommentFormType;
use DR\GitCommitNotification\Form\Review\AddCommentReplyFormType;
use DR\GitCommitNotification\Form\Review\EditCommentFormType;
use DR\GitCommitNotification\Form\Review\EditCommentReplyFormType;
use DR\GitCommitNotification\Model\Review\Action\AddCommentAction;
use DR\GitCommitNotification\Model\Review\Action\AddCommentReplyAction;
use DR\GitCommitNotification\Model\Review\Action\EditCommentAction;
use DR\GitCommitNotification\Model\Review\Action\EditCommentReplyAction;
use DR\GitCommitNotification\Repository\Review\CommentRepository;
use DR\GitCommitNotification\Service\CodeReview\DiffFinder;
use DR\GitCommitNotification\Utility\Assert;
use DR\GitCommitNotification\ViewModel\App\Review\AddCommentViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\CommentsViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\EditCommentReplyViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\EditCommentViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\ReplyCommentViewModel;
use Symfony\Component\Form\FormFactoryInterface;

class CommentViewModelProvider
{
    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly FormFactoryInterface $formFactory,
        private readonly DiffFinder $diffFinder,
    ) {
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
        $comments         = $this->commentRepository->findByReview($review, (string)($file->filePathBefore ?? $file->filePathAfter));
        $detachedComments = [];
        $groupedComments  = [];

        // 1) fine the DiffLine for the given LineReference
        // 2) if line exists, assign to grouped comments
        // 3) if not, add to detached comments
        foreach ($comments as $comment) {
            $line = $this->diffFinder->findLineInFile($file, Assert::notNull($comment->getLineReference()));
            if ($line !== null) {
                $groupedComments[spl_object_hash($line)][] = $comment;
            } else {
                $detachedComments[] = $comment;
            }
        }

        return new CommentsViewModel($groupedComments, $detachedComments);
    }
}

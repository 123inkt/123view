<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Form\Review\AddCommentReplyFormType;
use DR\Review\Form\Review\EditCommentFormType;
use DR\Review\Form\Review\EditCommentReplyFormType;
use DR\Review\Model\Review\Action\AddCommentReplyAction;
use DR\Review\Model\Review\Action\EditCommentAction;
use DR\Review\Model\Review\Action\EditCommentReplyAction;
use DR\Review\ViewModel\App\Comment\EditCommentReplyViewModel;
use DR\Review\ViewModel\App\Comment\EditCommentViewModel;
use DR\Review\ViewModel\App\Comment\ReplyCommentViewModel;
use Symfony\Component\Form\FormFactoryInterface;

class CommentViewModelProvider
{
    public function __construct(private readonly FormFactoryInterface $formFactory)
    {
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
}

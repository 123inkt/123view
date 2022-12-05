<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Comment;

use DR\Review\Entity\Review\CommentReply;
use Symfony\Component\Form\FormView;

/**
 * @codeCoverageIgnore
 */
class EditCommentReplyViewModel
{
    public function __construct(public readonly FormView $form, public readonly CommentReply $reply)
    {
    }
}

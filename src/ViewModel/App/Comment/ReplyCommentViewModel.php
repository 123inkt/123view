<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Comment;

use DR\Review\Entity\Review\Comment;
use Symfony\Component\Form\FormView;

/**
 * @codeCoverageIgnore
 */
class ReplyCommentViewModel
{
    public function __construct(public readonly FormView $form, public readonly Comment $comment)
    {
    }
}

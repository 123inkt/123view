<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Review;

use DR\GitCommitNotification\Entity\Review\Comment;
use Symfony\Component\Form\FormView;

class EditCommentViewModel
{
    public function __construct(public readonly FormView $form, public readonly Comment $comment)
    {
    }
}

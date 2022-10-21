<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Review;

use DR\GitCommitNotification\Entity\Git\Diff\DiffLine;
use Symfony\Component\Form\FormView;

class AddCommentViewModel
{
    public function __construct(public readonly FormView $form, public readonly DiffLine $diffLine)
    {
    }
}

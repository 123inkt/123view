<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Review;

use Symfony\Component\Form\FormView;

class BranchReviewViewModel
{
    public function __construct(public readonly FormView $form)
    {
    }
}

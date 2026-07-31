<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Admin;

use DR\Review\Entity\Ai\AiReviewReference;
use Symfony\Component\Form\FormView;

class EditAiReviewReferenceViewModel
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(public readonly AiReviewReference $reference, public readonly FormView $form)
    {
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Admin;

use DR\Review\Entity\Ai\AiReviewReference;
use DR\Review\Entity\Ai\AiReviewReferenceMatcher;
use Symfony\Component\Form\FormView;

class EditAiReviewReferenceMatcherViewModel
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(
        public readonly AiReviewReference $reference,
        public readonly AiReviewReferenceMatcher $matcher,
        public readonly FormView $form
    ) {
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Admin;

use DR\Review\Entity\Ai\AiReviewReference;
use DR\Review\Entity\Ai\AiReviewReferenceSection;
use Symfony\Component\Form\FormView;

class EditAiReviewReferenceSectionViewModel
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(
        public readonly AiReviewReference $reference,
        public readonly AiReviewReferenceSection $section,
        public readonly FormView $form
    ) {
    }
}

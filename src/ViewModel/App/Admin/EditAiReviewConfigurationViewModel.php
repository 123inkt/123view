<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Admin;

use DR\Review\Entity\Ai\AiReviewConfiguration;
use Symfony\Component\Form\FormView;

class EditAiReviewConfigurationViewModel
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(public readonly AiReviewConfiguration $configuration, public readonly FormView $form)
    {
    }
}

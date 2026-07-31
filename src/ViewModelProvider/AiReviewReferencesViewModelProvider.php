<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Repository\Ai\AiReviewReferenceRepository;
use DR\Review\ViewModel\App\Admin\AiReviewReferencesViewModel;

class AiReviewReferencesViewModelProvider
{
    public function __construct(private readonly AiReviewReferenceRepository $referenceRepository)
    {
    }

    public function getAiReviewReferencesViewModel(): AiReviewReferencesViewModel
    {
        return new AiReviewReferencesViewModel($this->referenceRepository->findBy([], ['priority' => 'DESC', 'id' => 'ASC']));
    }
}

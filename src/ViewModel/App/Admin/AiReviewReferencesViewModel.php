<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Admin;

use DR\Review\Entity\Ai\AiReviewReference;

class AiReviewReferencesViewModel
{
    /**
     * @codeCoverageIgnore
     *
     * @param AiReviewReference[] $references
     */
    public function __construct(public readonly array $references)
    {
    }
}

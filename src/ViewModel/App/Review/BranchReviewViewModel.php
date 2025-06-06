<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Review;

class BranchReviewViewModel
{
    /**
     * @param string[] $targetBranches
     */
    public function __construct(public readonly array $targetBranches)
    {
    }
}

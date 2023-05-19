<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Project;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;

class ProjectBranchesViewModel
{
    /**
     * @param string[]                  $branches
     * @param string[]                  $mergedBranches
     * @param array<string, CodeReview> $reviews
     */
    public function __construct(
        public readonly Repository $repository,
        public readonly array $branches,
        public readonly array $mergedBranches,
        private readonly array $reviews
    ) {
    }

    public function getReview(string $branchName): ?CodeReview
    {
        return $this->reviews[$branchName] ?? null;
    }

    public function isMerged(string $branchName): bool
    {
        return in_array($branchName, $this->mergedBranches, true);
    }
}

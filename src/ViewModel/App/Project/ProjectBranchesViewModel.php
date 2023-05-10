<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Project;

use DR\Review\Entity\Repository\Repository;

class ProjectBranchesViewModel
{
    /**
     * @param string[] $branches
     * @param string[] $mergedBranches
     */
    public function __construct(public readonly Repository $repository, public readonly array $branches, public readonly array $mergedBranches)
    {
    }

    public function isMerged(string $branchName): bool
    {
        return in_array($branchName, $this->mergedBranches, true);
    }
}

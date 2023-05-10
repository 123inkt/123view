<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\Branch\GitBranchService;
use DR\Review\ViewModel\App\Project\ProjectBranchesViewModel;

class ProjectBranchesViewModelProvider
{
    public function __construct(private readonly GitBranchService $branchService)
    {
    }

    /**
     * @throws RepositoryException
     */
    public function getProjectBranchesViewModel(Repository $repository): ProjectBranchesViewModel
    {
        $branches       = $this->branchService->getRemoteBranches($repository);
        $mergedBranches = $this->branchService->getRemoteBranches($repository, true);

        return new ProjectBranchesViewModel($repository, $branches, $mergedBranches);
    }
}

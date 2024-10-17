<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Branch;

use DR\Review\Doctrine\Type\RepositoryGitType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\ExternalTool\Gitlab\GitlabService;
use Throwable;

class BranchReviewTargetBranchService
{
    public function __construct(private readonly GitlabService $gitlabService)
    {
    }

    /**
     * @throws Throwable
     */
    public function getTargetBranch(Repository $repository, string $branchName): string
    {
        $targetBranchName = $repository->getMainBranchName();
        if ($repository->getGitType() === RepositoryGitType::GITLAB) {
            if (strrpos($branchName, '/') !== false) {
                $branchName = substr($branchName, strrpos($branchName, '/') + 1);
            }

            $projectId        = $repository->getRepositoryProperty('gitlab-project-id');
            $targetBranchName = $this->gitlabService->getMergeRequestTargetBranch((int)$projectId, $branchName);
        }

        return $targetBranchName;
    }
}

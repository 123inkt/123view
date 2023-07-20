<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Git\Branch\GitBranchService;
use DR\Utils\Arrays;
use DR\Review\ViewModel\App\Project\ProjectBranchesViewModel;

class ProjectBranchesViewModelProvider
{
    public function __construct(private readonly GitBranchService $branchService, private readonly CodeReviewRepository $reviewRepository)
    {
    }

    /**
     * @throws RepositoryException
     */
    public function getProjectBranchesViewModel(Repository $repository): ProjectBranchesViewModel
    {
        $branches       = $this->branchService->getRemoteBranches($repository);
        $mergedBranches = $this->branchService->getRemoteBranches($repository, true);

        $branchReviews = $this->reviewRepository->findBy(['repository' => $repository, 'type' => CodeReviewType::BRANCH, 'referenceId' => $branches]);
        $branchReviews = Arrays::reindex($branchReviews, static fn($review) => (string)$review->getReferenceId());

        return new ProjectBranchesViewModel($repository, $branches, $mergedBranches, $branchReviews);
    }
}

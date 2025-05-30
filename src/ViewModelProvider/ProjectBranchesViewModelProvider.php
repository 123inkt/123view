<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Git\Branch\CacheableGitBranchService;
use DR\Review\ViewModel\App\Project\ProjectBranchesViewModel;
use DR\Utils\Arrays;
use Throwable;

class ProjectBranchesViewModelProvider
{
    public function __construct(private readonly CacheableGitBranchService $branchService, private readonly CodeReviewRepository $reviewRepository)
    {
    }

    /**
     * @throws Throwable
     */
    public function getProjectBranchesViewModel(Repository $repository, ?string $searchQuery): ProjectBranchesViewModel
    {
        $branches       = $this->branchService->getRemoteBranches($repository);
        $mergedBranches = $this->branchService->getRemoteBranches($repository, true);

        // filter branches based on searchQuery
        if ($searchQuery !== null) {
            $branches = array_filter($branches, static fn(string $branch): bool => stripos($branch, $searchQuery) !== false);
        }

        $branchReviews = $this->reviewRepository->findBy(['repository' => $repository, 'type' => CodeReviewType::BRANCH, 'referenceId' => $branches]);
        $branchReviews = Arrays::reindex($branchReviews, static fn($review) => (string)$review->getReferenceId());

        return new ProjectBranchesViewModel($repository, $searchQuery, $branches, $mergedBranches, $branchReviews);
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Git\Branch\CacheableGitBranchService;
use DR\Review\ViewModel\App\Project\ProjectBranchesViewModel;
use DR\Utils\Arrays;
use DR\Utils\Assert;
use Throwable;

/**
 * @implements ProviderInterface<ProjectBranchesViewModel>
 */
// TODO angular remove non angular methods and implement only the provide method
class ProjectBranchesViewModelProvider implements ProviderInterface
{
    public function __construct(
        private readonly RepositoryRepository $repositoryRepository,
        private readonly CacheableGitBranchService $branchService,
        private readonly CodeReviewRepository $reviewRepository
    ) {
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

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $repositoryId = (int)Assert::numeric($uriVariables['repositoryId']);
        $repository   = Assert::notNull($this->repositoryRepository->find($repositoryId), 'Repository not found ' . $repositoryId);

        $branches       = $this->branchService->getRemoteBranches($repository);
        $mergedBranches = $this->branchService->getRemoteBranches($repository, true);

        $branchReviews = $this->reviewRepository->findBy(['repository' => $repository, 'type' => CodeReviewType::BRANCH, 'referenceId' => $branches]);
        $branchReviews = Arrays::reindex($branchReviews, static fn($review) => (string)$review->getReferenceId());

        return new ProjectBranchesViewModel($repository, '', $branches, $mergedBranches, $branchReviews);
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Exception\RepositoryException;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Git\Branch\GitBranchService;
use DR\Review\ViewModel\App\Search\SearchBranchViewModel;
use DR\Utils\Arrays;

class SearchBranchViewModelProvider
{
    public function __construct(
        private readonly RepositoryRepository $repositoryRepository,
        private readonly GitBranchService $branchService,
        private readonly CodeReviewRepository $reviewRepository
    ) {
    }

    /**
     * @throws RepositoryException
     */
    public function getSearchBranchViewModel(string $searchQuery): SearchBranchViewModel
    {
        $repositories = $this->repositoryRepository->findBy(['active' => true]);
        $repositories = Arrays::reindex($repositories, static fn(Repository $repository) => (int)$repository->getId());

        $branches = $this->getBranchesFor($repositories, $searchQuery);
        $reviews  = $this->getReviewsFor(array_merge(...$branches));

        return new SearchBranchViewModel($branches, $repositories, $reviews, $searchQuery);
    }

    /**
     * @param Repository[] $repositories
     *
     * @return array<int, string[]> [repositoryId => branchName[]]
     * @throws RepositoryException
     */
    private function getBranchesFor(array $repositories, string $searchQuery): array
    {
        $branches = [];
        foreach ($repositories as $repository) {
            // filter branches based on searchQuery
            $branchNames = array_filter(
                $this->branchService->getRemoteBranches($repository),
                static fn($branchName) => stripos($branchName, $searchQuery) !== false
            );
            if (count($branchNames) > 0) {
                $branches[(int)$repository->getId()] = $branchNames;
            }
        }

        return $branches;
    }

    /**
     * Find all the branch reviews for the given branches
     *
     * @param string[] $branches
     *
     * @return array<int, array<string, CodeReview>>
     */
    private function getReviewsFor(array $branches): array
    {
        if (count($branches) === 0) {
            return [];
        }

        $branchReviews = $this->reviewRepository->findBy(['type' => CodeReviewType::BRANCH, 'referenceId' => $branches]);

        // index by repository and branch name
        $groupedBranchReviews = [];
        foreach ($branchReviews as $branchReview) {
            $groupedBranchReviews[(int)$branchReview->getRepository()->getId()][(string)$branchReview->getReferenceId()] = $branchReview;
        }

        return $groupedBranchReviews;
    }
}

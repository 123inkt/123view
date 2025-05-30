<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Search;

use DR\Review\Controller\AbstractController;
use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Request\Search\SearchBranchRequest;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\Git\Branch\GitBranchService;
use DR\Review\ViewModel\App\Search\SearchBranchViewModel;
use DR\Review\ViewModel\App\Search\SearchCodeViewModel;
use Exception;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SearchBranchesController extends AbstractController
{
    public function __construct(
        private readonly RepositoryRepository $repositoryRepository,
        private readonly GitBranchService $branchService,
        private readonly CodeReviewRepository $reviewRepository
    ) {
    }

    /**
     * @return array{viewModel: SearchCodeViewModel}
     * @throws Exception
     */
    #[Route('app/branch/search', name: self::class, methods: 'GET')]
    #[Template('app/search/branch.search.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(SearchBranchRequest $request): array
    {
        $repositories = $this->repositoryRepository->findBy(['active' => true]);

        $branches = [];
        foreach ($repositories as $repository) {
            // filter branches based on searchQuery
            $branchNames = array_filter(
                $this->branchService->getRemoteBranches($repository),
                static fn($branchName) => stripos($branchName, $request->getSearchQuery()) !== false
            );
            if (count($branchNames) > 0) {
                $branches[$repository->getId()] = $branchNames;
            }
        }

        // find all reviews for the branches
        if (count($branches) > 0) {
            $branchReviews = $this->reviewRepository->findBy(['type' => CodeReviewType::BRANCH, 'referenceId' => array_merge(...$branches)]);
            // index by repository and branch name
            $groupedBranchReviews = [];
            foreach ($branchReviews as $branchReview) {
                $groupedBranchReviews[$branchReview->getRepository()->getId()][$branchReview->getReferenceId()] = $branchReview;
            }
        } else {
            $groupedBranchReviews = [];
        }

        return ['viewModel' => new SearchBranchViewModel($branches, $repositories, $groupedBranchReviews, $request->getSearchQuery())];
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Search;

use DR\Review\Controller\AbstractController;
use DR\Review\Request\Search\SearchBranchRequest;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\App\Search\SearchBranchViewModel;
use DR\Review\ViewModelProvider\SearchBranchViewModelProvider;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

class SearchBranchesController extends AbstractController
{
    public function __construct(private readonly SearchBranchViewModelProvider $viewModelProvider)
    {
    }

    /**
     * @return array{viewModel: SearchBranchViewModel}
     * @throws Throwable
     */
    #[Route('app/branch/search', name: self::class, methods: 'GET')]
    #[Template('app/search/branch.search.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(SearchBranchRequest $request): array
    {
        return ['viewModel' => $this->viewModelProvider->getSearchBranchViewModel($request->getSearchQuery())];
    }
}

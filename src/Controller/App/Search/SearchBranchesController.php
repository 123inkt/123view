<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Search;

use DR\Review\Controller\AbstractController;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Request\Search\SearchBranchRequest;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\App\Search\SearchBranchViewModel;
use DR\Review\ViewModel\App\Search\SearchCodeViewModel;
use Exception;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Contracts\Translation\TranslatorInterface;

class SearchBranchesController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly RepositoryRepository $repositoryRepository,
        private readonly ?Stopwatch $stopwatch
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
        return ['viewModel' => new SearchBranchViewModel([], $request->getSearchQuery())];
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Search;

use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\Git\GitRepositoryCacheLocationService;
use Exception;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SearchCodeController
{
    public function __construct(
        private readonly RepositoryRepository $repository
    ) {
    }

    /**
     * @throws Exception
     */
    #[Route('app/code/search', name: self::class, methods: 'GET')]
    #[Template('app/search/code.search.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request): array
    {
        $repositories = $this->repository->findBy(['active' => true]);

        $finder = new Finder();

        //$finder->files()->ignoreDotFiles(false)->in($cacheDirectory)->exclude(['.git/']);

        return [];
    }
}

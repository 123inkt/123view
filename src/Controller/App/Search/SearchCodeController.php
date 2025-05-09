<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Search;

use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\Search\RipGrep\GitFileSearcher;
use Exception;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Stopwatch\Stopwatch;

class SearchCodeController
{
    public function __construct(
        private readonly GitFileSearcher $fileSearcher,
        private readonly RepositoryRepository $repositoryRepository,
        private readonly ?Stopwatch $stopwatch
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
        $searchQuery = $request->query->getString('search');
        if (strlen($searchQuery) < 3) {
            throw new BadRequestHttpException('Search query must be at least 3 characters');
        }

        $this->stopwatch?->start('finder');

        $repositories = $this->repositoryRepository->findBy(['active' => true]);
        $lines        = $this->fileSearcher->find($searchQuery, $repositories);

        $this->stopwatch?->stop('finder');

        return ['files' => $lines];
    }
}

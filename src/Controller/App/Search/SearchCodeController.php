<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Search;

use DR\Review\Controller\AbstractController;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\Search\RipGrep\GitFileSearcher;
use DR\Review\ViewModel\App\Search\SearchCodeViewModel;
use Exception;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Contracts\Translation\TranslatorInterface;

class SearchCodeController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
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
        $searchQuery = trim($request->query->getString('search'));
        if (strlen($searchQuery) < 5) {
            $this->addFlash('error', $this->translator->trans('search.much.be.minimum.5.characters'));
            $files = [];
        } else {
            $this->stopwatch?->start('finder');

            $repositories = $this->repositoryRepository->findBy(['active' => true]);
            $files        = $this->fileSearcher->find($searchQuery, $repositories);

            $this->stopwatch?->stop('finder');
        }

        return ['viewModel' => new SearchCodeViewModel($files, $searchQuery)];
    }
}

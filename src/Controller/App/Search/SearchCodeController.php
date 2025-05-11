<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Search;

use DR\Review\Controller\AbstractController;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Request\Search\SearchCodeRequest;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\Search\RipGrep\GitFileSearcher;
use DR\Review\ViewModel\App\Search\SearchCodeViewModel;
use Exception;
use Symfony\Bridge\Twig\Attribute\Template;
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
     * @return array{viewModel: SearchCodeViewModel}
     * @throws Exception
     */
    #[Route('app/code/search', name: self::class, methods: 'GET')]
    #[Template('app/search/code.search.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(SearchCodeRequest $request): array
    {
        $searchQuery = $request->getSearchQuery();
        $extension   = $request->getExtension();
        if (strlen($searchQuery) < 5) {
            $this->addFlash('error', $this->translator->trans('search.much.be.minimum.5.characters'));
            $files = [];
        } else {
            $this->stopwatch?->start('file-search');

            $repositories = $this->repositoryRepository->findBy(['active' => true]);
            $files        = $this->fileSearcher->find($searchQuery, $extension, $repositories);

            $this->stopwatch?->stop('file-search');
        }

        return ['viewModel' => new SearchCodeViewModel($files, $searchQuery, $extension)];
    }
}

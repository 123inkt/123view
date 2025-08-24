<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Search;

use DR\Review\Controller\AbstractController;
use DR\Review\Model\Search\SearchResultCollection;
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
        $extensions  = $request->getExtensions();
        if (strlen($searchQuery) < 5) {
            $this->addFlash('error', $this->translator->trans('search.much.be.minimum.5.characters'));
            $results = new SearchResultCollection([], false);
        } else {
            $this->stopwatch?->start('file-search');

            $repositories = $this->repositoryRepository->findBy(['active' => true]);
            $results      = $this->fileSearcher->find($searchQuery, $extensions, $repositories, $request->isShowAll() ? null : 100);

            $this->stopwatch?->stop('file-search');
        }

        return [
            'page_title' => $this->translator->trans('code.search'),
            'viewModel'  => new SearchCodeViewModel($results, $searchQuery, $extensions === null ? null : implode(',', $extensions))
        ];
    }
}

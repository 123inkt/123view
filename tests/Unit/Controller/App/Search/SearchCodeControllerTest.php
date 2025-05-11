<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Search;

use DR\PHPUnitExtensions\Symfony\AbstractControllerTestCase;
use DR\Review\Controller\App\Search\SearchCodeController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Model\Search\SearchResult;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Request\Search\SearchCodeRequest;
use DR\Review\Service\Search\RipGrep\GitFileSearcher;
use DR\Review\ViewModel\App\Search\SearchCodeViewModel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractControllerTestCase<SearchCodeController>
 */
#[CoversClass(SearchCodeController::class)]
class SearchCodeControllerTest extends AbstractControllerTestCase
{
    private TranslatorInterface&MockObject  $translator;
    private GitFileSearcher&MockObject      $fileSearcher;
    private RepositoryRepository&MockObject $repositoryRepository;

    protected function setUp(): void
    {
        $this->translator           = $this->createMock(TranslatorInterface::class);
        $this->fileSearcher         = $this->createMock(GitFileSearcher::class);
        $this->repositoryRepository = $this->createMock(RepositoryRepository::class);
        parent::setUp();
    }

    public function testInvokeWithTooShortQuery(): void
    {
        $request = $this->createMock(SearchCodeRequest::class);
        $request->method('getSearchQuery')->willReturn('fail');
        $request->method('getExtensions')->willReturn(null);

        $this->translator->expects($this->once())->method('trans')->with('search.much.be.minimum.5.characters')->willReturn('translation');
        $this->expectAddFlash('error', 'translation');
        $this->fileSearcher->expects($this->never())->method('find');

        $result = ($this->controller)($request);

        static::assertEquals(['viewModel' => new SearchCodeViewModel([], 'fail', null)], $result);
    }

    public function testInvokeWithSearch(): void
    {
        $request = $this->createMock(SearchCodeRequest::class);
        $request->method('getSearchQuery')->willReturn('success');
        $request->method('getExtensions')->willReturn(['json', 'yaml']);

        $repository    = new Repository();
        $searchResults = [new SearchResult($repository, new SplFileInfo('file', '', ''))];

        $this->translator->expects($this->never())->method('trans');
        $this->repositoryRepository->expects($this->once())->method('findBy')->with(['active' => true])->willReturn([$repository]);
        $this->fileSearcher->expects($this->once())->method('find')->with('success', ['json', 'yaml'], [$repository])->willReturn($searchResults);

        $result = ($this->controller)($request);

        static::assertEquals(['viewModel' => new SearchCodeViewModel($searchResults, 'success', 'json,yaml')], $result);
    }

    public function getController(): AbstractController
    {
        return new SearchCodeController($this->translator, $this->fileSearcher, $this->repositoryRepository, null);
    }
}

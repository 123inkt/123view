<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Search;

use DR\PHPUnitExtensions\Symfony\AbstractControllerTestCase;
use DR\Review\Controller\App\Search\SearchCodeController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Model\Search\SearchResultCollection;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Request\Search\SearchCodeRequest;
use DR\Review\Service\Search\RipGrep\GitFileSearcher;
use DR\Review\ViewModel\App\Search\SearchCodeViewModel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use function DR\PHPUnitExtensions\Mock\consecutive;

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
        $request = static::createStub(SearchCodeRequest::class);
        $request->method('getSearchQuery')->willReturn('fail');
        $request->method('getExtensions')->willReturn(null);
        $request->method('isShowAll')->willReturn(false);

        $this->translator->expects($this->exactly(2))->method('trans')
            ->with(...consecutive(['search.much.be.minimum.5.characters'], ['code.search']))
            ->willReturn('translation1', 'translation2');
        $this->expectAddFlash('error', 'translation1');
        $this->fileSearcher->expects($this->never())->method('find');
        $this->repositoryRepository->expects($this->never())->method('findBy');

        $result = ($this->controller)($request);

        static::assertEquals(
            ['page_title' => 'translation2', 'viewModel' => new SearchCodeViewModel(new SearchResultCollection([], false), 'fail', null)],
            $result
        );
    }

    public function testInvokeWithSearch(): void
    {
        $request = static::createStub(SearchCodeRequest::class);
        $request->method('getSearchQuery')->willReturn('success');
        $request->method('getExtensions')->willReturn(['json', 'yaml']);
        $request->method('isShowAll')->willReturn(false);

        $repository    = new Repository();
        $searchResults = static::createStub(SearchResultCollection::class);

        $this->translator->expects($this->once())->method('trans')->with('code.search')->willReturn('translation');
        $this->repositoryRepository->expects($this->once())->method('findBy')->with(['active' => true])->willReturn([$repository]);
        $this->fileSearcher->expects($this->once())->method('find')
            ->with('success', ['json', 'yaml'], [$repository], 100)
            ->willReturn($searchResults);

        $result = ($this->controller)($request);

        static::assertEquals(
            ['page_title' => 'translation', 'viewModel' => new SearchCodeViewModel($searchResults, 'success', 'json,yaml')],
            $result
        );
    }

    public function getController(): AbstractController
    {
        return new SearchCodeController($this->translator, $this->fileSearcher, $this->repositoryRepository, null);
    }
}

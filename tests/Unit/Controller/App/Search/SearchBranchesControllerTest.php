<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Search;

use DR\Review\Controller\App\Search\SearchBranchesController;
use DR\Review\Request\Search\SearchBranchRequest;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\Search\SearchBranchViewModel;
use DR\Review\ViewModelProvider\SearchBranchViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractControllerTestCase<SearchBranchesController>
 */
#[CoversClass(SearchBranchesController::class)]
class SearchBranchesControllerTest extends AbstractControllerTestCase
{
    private TranslatorInterface&MockObject           $translator;
    private SearchBranchViewModelProvider&MockObject $viewModelProvider;

    protected function setUp(): void
    {
        $this->translator        = $this->createMock(TranslatorInterface::class);
        $this->viewModelProvider = $this->createMock(SearchBranchViewModelProvider::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $request = static::createStub(SearchBranchRequest::class);
        $request->method('getSearchQuery')->willReturn('test-query');

        $model = static::createStub(SearchBranchViewModel::class);

        $this->translator->expects($this->once())->method('trans')->with('branch.search')->willReturn('translation');
        $this->viewModelProvider->expects($this->once())->method('getSearchBranchViewModel')->with('test-query')->willReturn($model);

        $result = ($this->controller)($request);
        static::assertSame(['page_title' => 'translation', 'viewModel' => $model], $result);
    }

    public function getController(): AbstractController
    {
        return new SearchBranchesController($this->translator, $this->viewModelProvider);
    }
}

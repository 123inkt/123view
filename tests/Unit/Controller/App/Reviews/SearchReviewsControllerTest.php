<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Reviews;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Reviews\SearchReviewsController;
use DR\Review\QueryParser\InvalidQueryException;
use DR\Review\QueryParser\ParserHasFailedFormatter;
use DR\Review\QueryParser\Term\TermInterface;
use DR\Review\Request\Reviews\SearchReviewsRequest;
use DR\Review\Service\CodeReview\Search\ReviewSearchQueryTermFactory;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\Review\ReviewsViewModel;
use DR\Review\ViewModelProvider\ReviewsViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(SearchReviewsController::class)]
class SearchReviewsControllerTest extends AbstractControllerTestCase
{
    private ReviewsViewModelProvider&MockObject     $viewModelProvider;
    private ReviewSearchQueryTermFactory&MockObject $termFactory;
    private ParserHasFailedFormatter&MockObject     $failedFormatter;

    public function setUp(): void
    {
        $this->viewModelProvider = $this->createMock(ReviewsViewModelProvider::class);
        $this->termFactory       = $this->createMock(ReviewSearchQueryTermFactory::class);
        $this->failedFormatter   = $this->createMock(ParserHasFailedFormatter::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $request = $this->createMock(SearchReviewsRequest::class);
        $request->method('getSearchQuery')->willReturn('search');
        $terms     = $this->createMock(TermInterface::class);
        $viewModel = $this->createMock(ReviewsViewModel::class);

        $this->termFactory->expects(self::once())->method('getSearchTerms')->with('search')->willReturn($terms);
        $this->viewModelProvider->expects(self::once())->method('getSearchReviewsViewModel')->with($request, $terms)->willReturn($viewModel);

        $result = ($this->controller)($request);

        static::assertSame(['reviewsModel' => $viewModel], $result);
    }

    public function testInvokeBadQuery(): void
    {
        $request = $this->createMock(SearchReviewsRequest::class);
        $request->method('getSearchQuery')->willReturn('search');
        $viewModel = $this->createMock(ReviewsViewModel::class);
        $failure   = $this->createMock(InvalidQueryException::class);

        $this->expectAddFlash('error', 'failure');
        $this->termFactory->expects(self::once())->method('getSearchTerms')->with('search')->willThrowException($failure);
        $this->failedFormatter->expects(self::once())->method('format')->with($failure)->willReturn('failure');
        $this->viewModelProvider->expects(self::once())->method('getSearchReviewsViewModel')->with($request, null)->willReturn($viewModel);

        $result = ($this->controller)($request);

        static::assertSame(['reviewsModel' => $viewModel], $result);
    }

    public function getController(): AbstractController
    {
        return new SearchReviewsController($this->viewModelProvider, $this->termFactory, $this->failedFormatter);
    }
}

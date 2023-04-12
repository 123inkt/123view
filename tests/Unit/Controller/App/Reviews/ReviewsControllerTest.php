<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Reviews;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Reviews\ReviewsController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Model\Page\Breadcrumb;
use DR\Review\QueryParser\InvalidQueryException;
use DR\Review\QueryParser\ParserHasFailedFormatter;
use DR\Review\QueryParser\Term\TermInterface;
use DR\Review\Request\Reviews\SearchReviewsRequest;
use DR\Review\Service\CodeReview\Search\ReviewSearchQueryTermFactory;
use DR\Review\Service\Page\BreadcrumbFactory;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\Review\ReviewsViewModel;
use DR\Review\ViewModelProvider\ReviewsViewModelProvider;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Controller\App\Reviews\ReviewsController
 * @covers ::__construct
 */
class ReviewsControllerTest extends AbstractControllerTestCase
{
    private ReviewsViewModelProvider&MockObject     $viewModelProvider;
    private BreadcrumbFactory&MockObject            $breadcrumbFactory;
    private ReviewSearchQueryTermFactory&MockObject $termFactory;
    private ParserHasFailedFormatter&MockObject     $failedFormatter;

    public function setUp(): void
    {
        $this->viewModelProvider = $this->createMock(ReviewsViewModelProvider::class);
        $this->breadcrumbFactory = $this->createMock(BreadcrumbFactory::class);
        $this->termFactory       = $this->createMock(ReviewSearchQueryTermFactory::class);
        $this->failedFormatter   = $this->createMock(ParserHasFailedFormatter::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $repository->setDisplayName('repository');
        $viewModel  = $this->createMock(ReviewsViewModel::class);
        $breadcrumb = new Breadcrumb('label', 'url');
        $terms      = $this->createMock(TermInterface::class);
        $request    = $this->createMock(SearchReviewsRequest::class);
        $request->method('getSearchQuery')->willReturn('searchQuery');

        $this->termFactory->expects(self::once())->method('getSearchTerms')->with('searchQuery')->willReturn($terms);
        $this->viewModelProvider
            ->expects(self::once())
            ->method('getReviewsViewModel')
            ->with($request, $terms, $repository)
            ->willReturn($viewModel);
        $this->breadcrumbFactory->expects(self::once())->method('createForReviews')->with($repository)->willReturn([$breadcrumb]);

        $result = ($this->controller)($request, $repository);
        static::assertSame('Repository', $result['page_title']);
        static::assertSame([$breadcrumb], $result['breadcrumbs']);
        static::assertSame($viewModel, $result['reviewsModel']);
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeBadTerms(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $repository->setDisplayName('repository');
        $viewModel  = $this->createMock(ReviewsViewModel::class);
        $breadcrumb = new Breadcrumb('label', 'url');
        $request    = $this->createMock(SearchReviewsRequest::class);
        $request->method('getSearchQuery')->willReturn('searchQuery');
        $failure = $this->createMock(InvalidQueryException::class);

        $this->expectAddFlash('error', 'failure');
        $this->termFactory->expects(self::once())->method('getSearchTerms')->with('searchQuery')->willThrowException($failure);
        $this->failedFormatter->expects(self::once())->method('format')->with($failure)->willReturn('failure');
        $this->viewModelProvider
            ->expects(self::once())
            ->method('getReviewsViewModel')
            ->with($request, null, $repository)
            ->willReturn($viewModel);
        $this->breadcrumbFactory->expects(self::once())->method('createForReviews')->with($repository)->willReturn([$breadcrumb]);

        $result = ($this->controller)($request, $repository);
        static::assertSame('Repository', $result['page_title']);
        static::assertSame([$breadcrumb], $result['breadcrumbs']);
        static::assertSame($viewModel, $result['reviewsModel']);
    }

    public function getController(): AbstractController
    {
        return new ReviewsController($this->viewModelProvider, $this->breadcrumbFactory, $this->termFactory, $this->failedFormatter);
    }
}

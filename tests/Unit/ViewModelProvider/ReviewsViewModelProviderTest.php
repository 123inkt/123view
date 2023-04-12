<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use Doctrine\ORM\Tools\Pagination\Paginator;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\User\User;
use DR\Review\QueryParser\Term\TermInterface;
use DR\Review\Repository\Review\CodeReviewQueryBuilder;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Request\Reviews\SearchReviewsRequest;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\Timeline\TimelineViewModel;
use DR\Review\ViewModelProvider\ReviewsViewModelProvider;
use DR\Review\ViewModelProvider\ReviewTimelineViewModelProvider;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\ViewModelProvider\ReviewsViewModelProvider
 * @covers ::__construct
 */
class ReviewsViewModelProviderTest extends AbstractTestCase
{
    private User                                       $user;
    private CodeReviewRepository&MockObject            $reviewRepository;
    private ReviewTimelineViewModelProvider&MockObject $timelineViewModelProvider;
    private ReviewsViewModelProvider                   $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user                      = new User();
        $this->reviewRepository          = $this->createMock(CodeReviewRepository::class);
        $this->timelineViewModelProvider = $this->createMock(ReviewTimelineViewModelProvider::class);
        $this->provider                  = new ReviewsViewModelProvider($this->user, $this->reviewRepository, $this->timelineViewModelProvider);
    }

    /**
     * @covers ::getSearchReviewsViewModel
     */
    public function testGetSearchReviewsViewModel(): void
    {
        $paginator = $this->createMock(Paginator::class);

        $request = $this->createMock(SearchReviewsRequest::class);
        $request->method('getPage')->willReturn(5);
        $request->method('getOrderBy')->willReturn(CodeReviewQueryBuilder::ORDER_CREATE_TIMESTAMP);
        $request->method('getSearchQuery')->willReturn('search');

        $terms = $this->createMock(TermInterface::class);

        $this->reviewRepository->expects(self::once())
            ->method('getPaginatorForSearchQuery')
            ->with(null, 5, $terms, CodeReviewQueryBuilder::ORDER_CREATE_TIMESTAMP)
            ->willReturn($paginator);

        $viewModel = $this->provider->getSearchReviewsViewModel($request, $terms);
        static::assertNotNull($viewModel->paginator);
        static::assertSame('search', $viewModel->searchQuery);
    }

    /**
     * @covers ::getReviewsViewModel
     */
    public function testGetReviewsViewModel(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $repository->setDisplayName('repository');
        $paginator = $this->createMock(Paginator::class);
        $timeline  = $this->createMock(TimelineViewModel::class);

        $request = $this->createMock(SearchReviewsRequest::class);
        $request->method('getPage')->willReturn(5);
        $request->method('getOrderBy')->willReturn(CodeReviewQueryBuilder::ORDER_CREATE_TIMESTAMP);
        $request->method('getSearchQuery')->willReturn('search');

        $terms = $this->createMock(TermInterface::class);

        $this->reviewRepository->expects(self::once())
            ->method('getPaginatorForSearchQuery')
            ->with(123, 5, $terms, CodeReviewQueryBuilder::ORDER_CREATE_TIMESTAMP)
            ->willReturn($paginator);

        $this->timelineViewModelProvider->expects(self::once())
            ->method('getTimelineViewModelForFeed')
            ->with($this->user, static::callback(static fn($arg) => count($arg) > 0), $repository)
            ->willReturn($timeline);

        $viewModel = $this->provider->getReviewsViewModel($request, $terms, $repository);
        static::assertSame($repository, $viewModel->repository);
        static::assertNotNull($viewModel->paginator);
        static::assertSame('search', $viewModel->searchQuery);
        static::assertSame($timeline, $viewModel->timeline);
    }
}

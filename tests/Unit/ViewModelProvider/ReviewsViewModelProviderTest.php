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
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\Timeline\TimelineViewModel;
use DR\Review\ViewModelProvider\ReviewsViewModelProvider;
use DR\Review\ViewModelProvider\ReviewTimelineViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(ReviewsViewModelProvider::class)]
class ReviewsViewModelProviderTest extends AbstractTestCase
{
    private User                                       $user;
    private UserEntityProvider&MockObject             $userProvider;
    private CodeReviewRepository&MockObject            $reviewRepository;
    private ReviewTimelineViewModelProvider&MockObject $timelineViewModelProvider;
    private ReviewsViewModelProvider                   $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user                      = new User();
        $this->userProvider              = $this->createMock(UserEntityProvider::class);
        $this->reviewRepository          = $this->createMock(CodeReviewRepository::class);
        $this->timelineViewModelProvider = $this->createMock(ReviewTimelineViewModelProvider::class);
        $this->provider                  = new ReviewsViewModelProvider(
            $this->userProvider,
            $this->reviewRepository,
            $this->timelineViewModelProvider
        );
    }

    public function testGetSearchReviewsViewModel(): void
    {
        $paginator = static::createStub(Paginator::class);

        $request = static::createStub(SearchReviewsRequest::class);
        $request->method('getPage')->willReturn(5);
        $request->method('getOrderBy')->willReturn(CodeReviewQueryBuilder::ORDER_CREATE_TIMESTAMP);
        $request->method('getSearchQuery')->willReturn('search');

        $terms = static::createStub(TermInterface::class);

        $this->reviewRepository->expects($this->once())
            ->method('getPaginatorForSearchQuery')
            ->with(null, 5, $terms, CodeReviewQueryBuilder::ORDER_CREATE_TIMESTAMP)
            ->willReturn($paginator);
        $this->userProvider->expects($this->never())->method('getCurrentUser');
        $this->timelineViewModelProvider->expects($this->never())->method('getTimelineViewModelForFeed');

        $viewModel = $this->provider->getSearchReviewsViewModel($request, $terms);
        static::assertNotNull($viewModel->paginator);
        static::assertSame('search', $viewModel->searchQuery);
    }

    public function testGetReviewsViewModel(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $repository->setDisplayName('repository');
        $paginator = static::createStub(Paginator::class);
        $timeline  = static::createStub(TimelineViewModel::class);

        $request = static::createStub(SearchReviewsRequest::class);
        $request->method('getPage')->willReturn(5);
        $request->method('getOrderBy')->willReturn(CodeReviewQueryBuilder::ORDER_CREATE_TIMESTAMP);
        $request->method('getSearchQuery')->willReturn('search');

        $terms = static::createStub(TermInterface::class);

        $this->userProvider->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($this->user);
        $this->reviewRepository->expects($this->once())
            ->method('getPaginatorForSearchQuery')
            ->with(123, 5, $terms, CodeReviewQueryBuilder::ORDER_CREATE_TIMESTAMP)
            ->willReturn($paginator);

        $this->timelineViewModelProvider->expects($this->once())
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

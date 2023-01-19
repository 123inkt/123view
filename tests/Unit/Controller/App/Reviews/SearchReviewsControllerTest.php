<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Reviews;

use Doctrine\ORM\Tools\Pagination\Paginator;
use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Reviews\SearchReviewsController;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CodeReviewQueryBuilder;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Request\Reviews\SearchReviewsRequest;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\Review\ReviewsViewModel;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Controller\App\Reviews\SearchReviewsController
 * @covers ::__construct
 */
class SearchReviewsControllerTest extends AbstractControllerTestCase
{
    private CodeReviewRepository&MockObject $reviewRepository;

    public function setUp(): void
    {
        $this->reviewRepository = $this->createMock(CodeReviewRepository::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $user      = new User();
        $paginator = $this->createMock(Paginator::class);

        $this->expectGetUser($user);
        $this->reviewRepository->expects(self::once())
            ->method('getPaginatorForSearchQuery')
            ->with($user, null, 5, 'search', CodeReviewQueryBuilder::ORDER_CREATE_TIMESTAMP)
            ->willReturn($paginator);

        $request = $this->createMock(SearchReviewsRequest::class);
        $request->method('getPage')->willReturn(5);
        $request->method('getOrderBy')->willReturn(CodeReviewQueryBuilder::ORDER_CREATE_TIMESTAMP);
        $request->method('getSearchQuery')->willReturn('search');

        $result = ($this->controller)($request);

        /** @var ReviewsViewModel $viewModel */
        $viewModel = $result['reviewsModel'];
        static::assertNull($viewModel->repository);
        static::assertSame('search', $viewModel->searchQuery);
    }

    public function getController(): AbstractController
    {
        return new SearchReviewsController($this->reviewRepository);
    }
}

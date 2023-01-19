<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Reviews;

use Doctrine\ORM\Tools\Pagination\Paginator;
use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Reviews\ReviewsController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\User\User;
use DR\Review\Model\Page\Breadcrumb;
use DR\Review\Repository\Review\CodeReviewQueryBuilder;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Request\Reviews\SearchReviewsRequest;
use DR\Review\Service\Page\BreadcrumbFactory;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\Review\ReviewsViewModel;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Controller\App\Reviews\ReviewsController
 * @covers ::__construct
 */
class ReviewsControllerTest extends AbstractControllerTestCase
{
    private CodeReviewRepository&MockObject $reviewRepository;
    private BreadcrumbFactory&MockObject    $breadcrumbFactory;

    public function setUp(): void
    {
        $this->reviewRepository  = $this->createMock(CodeReviewRepository::class);
        $this->breadcrumbFactory = $this->createMock(BreadcrumbFactory::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $user       = new User();
        $repository = new Repository();
        $repository->setId(123);
        $repository->setDisplayName('repository');
        $paginator  = $this->createMock(Paginator::class);
        $breadcrumb = new Breadcrumb('label', 'url');

        $this->expectGetUser($user);
        $this->reviewRepository->expects(self::once())
            ->method('getPaginatorForSearchQuery')
            ->with($user, 123, 5, 'search', CodeReviewQueryBuilder::ORDER_CREATE_TIMESTAMP)
            ->willReturn($paginator);
        $this->breadcrumbFactory->expects(self::once())->method('createForReviews')->with($repository)->willReturn([$breadcrumb]);

        $request = $this->createMock(SearchReviewsRequest::class);
        $request->method('getPage')->willReturn(5);
        $request->method('getOrderBy')->willReturn(CodeReviewQueryBuilder::ORDER_CREATE_TIMESTAMP);
        $request->method('getSearchQuery')->willReturn('search');

        $result = ($this->controller)($request, $repository);
        static::assertSame('Repository', $result['page_title']);
        static::assertSame([$breadcrumb], $result['breadcrumbs']);

        /** @var ReviewsViewModel $viewModel */
        $viewModel = $result['reviewsModel'];
        static::assertSame($repository, $viewModel->repository);
        static::assertSame('search', $viewModel->searchQuery);
    }

    public function getController(): AbstractController
    {
        return new ReviewsController($this->reviewRepository, $this->breadcrumbFactory);
    }
}

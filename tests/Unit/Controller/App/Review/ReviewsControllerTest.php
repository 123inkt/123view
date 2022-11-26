<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\App\Review;

use Doctrine\ORM\Tools\Pagination\Paginator;
use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Controller\App\Review\ReviewsController;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Model\Page\Breadcrumb;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Service\Page\BreadcrumbFactory;
use DR\GitCommitNotification\Tests\AbstractControllerTestCase;
use DR\GitCommitNotification\ViewModel\App\Review\ReviewsViewModel;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\App\Review\ReviewsController
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
            ->with($user, 123, 5, 'search')
            ->willReturn($paginator);
        $this->breadcrumbFactory->expects(self::once())->method('createForReviews')->with($repository)->willReturn([$breadcrumb]);

        $result = ($this->controller)(new Request(['search' => 'search', 'page' => 5]), $repository);
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

<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Reviews;

use Doctrine\ORM\Tools\Pagination\Paginator;
use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Reviews\SearchReviewsController;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\Review\ReviewsViewModel;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

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
            ->with($user, null, 5, 'search')
            ->willReturn($paginator);

        $result = ($this->controller)(new Request(['search' => 'search', 'page' => 5]));

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

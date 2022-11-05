<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModel\App\Review;

use ArrayIterator;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\ViewModel\App\Review\PaginatorViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\ReviewsViewModel;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ViewModel\App\Review\ReviewsViewModel
 * @covers ::__construct
 */
class ReviewsViewModelTest extends AbstractTestCase
{
    /**
     * @covers ::getReviews
     * @covers ::getRepository
     * @covers ::getPaginator
     * @covers ::getSearchQuery
     */
    public function testAccessorPairs(): void
    {
        $reviews    = [new CodeReview()];
        $repository = new Repository();
        $paginator  = $this->createMock(Paginator::class);
        $paginator->method('getIterator')->willReturn(new ArrayIterator($reviews));
        $paginatorVm = $this->createMock(PaginatorViewModel::class);
        $searchQuery = 'foobar';

        $viewModel = new ReviewsViewModel($repository, $paginator, $paginatorVm, $searchQuery);

        static::assertSame($reviews, $viewModel->getReviews());
        static::assertSame($repository, $viewModel->getRepository());
        static::assertSame($paginatorVm, $viewModel->getPaginator());
        static::assertSame('foobar', $viewModel->getSearchQuery());
    }
}

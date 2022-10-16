<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModel\App\Review;

use ArrayIterator;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\ViewModel\App\Review\ReviewsViewModel;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ViewModel\App\Review\ReviewsViewModel
 * @covers ::__construct
 */
class ReviewsViewModelTest extends AbstractTestCase
{
    /**
     * @covers ::getReviews
     * @covers ::getPage
     * @covers ::getSearchQuery
     */
    public function testAccessorPairs(): void
    {
        $reviews   = [new CodeReview()];
        $paginator = $this->createMock(Paginator::class);
        $paginator->method('getIterator')->willReturn(new ArrayIterator($reviews));
        $page        = 5;
        $searchQuery = 'foobar';

        $viewModel = new ReviewsViewModel($paginator, $page, $searchQuery);

        static::assertSame($reviews, $viewModel->getReviews());
        static::assertSame($page, $viewModel->getPage());
        static::assertSame('foobar', $viewModel->getSearchQuery());
    }
}

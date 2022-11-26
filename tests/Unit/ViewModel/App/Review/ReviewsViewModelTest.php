<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModel\App\Review;

use ArrayIterator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Revision;
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
    }

    /**
     * @covers ::getAuthors
     */
    public function testGetAuthors(): void
    {
        $reviews    = [new CodeReview()];
        $repository = new Repository();
        $paginator  = $this->createMock(Paginator::class);
        $paginator->method('getIterator')->willReturn(new ArrayIterator($reviews));
        $paginatorVm = $this->createMock(PaginatorViewModel::class);
        $viewModel   = new ReviewsViewModel($repository, $paginator, $paginatorVm, '');

        $revisionA = new Revision();
        $revisionB = new Revision();
        $revisionC = new Revision();

        $revisionA->setAuthorName('Sherlock');
        $revisionB->setAuthorName('Sherlock');
        $revisionC->setAuthorName('Watson');

        static::assertSame(['Sherlock', 'Watson'], array_values($viewModel->getAuthors(new ArrayCollection([$revisionA, $revisionB, $revisionC]))));
    }
}

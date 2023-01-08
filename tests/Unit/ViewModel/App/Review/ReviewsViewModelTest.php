<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Review;

use ArrayIterator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\PaginatorViewModel;
use DR\Review\ViewModel\App\Review\ReviewsViewModel;

/**
 * @coversDefaultClass \DR\Review\ViewModel\App\Review\ReviewsViewModel
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

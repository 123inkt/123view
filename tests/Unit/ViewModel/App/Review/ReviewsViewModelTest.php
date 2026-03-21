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
use DR\Review\ViewModel\App\Review\Timeline\TimelineViewModel;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ReviewsViewModel::class)]
class ReviewsViewModelTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        $reviews    = [new CodeReview()];
        $repository = new Repository();
        $paginator  = static::createStub(Paginator::class);
        $paginator->method('getIterator')->willReturn(new ArrayIterator($reviews));
        $paginatorVm = static::createStub(PaginatorViewModel::class);
        $timeline    = static::createStub(TimelineViewModel::class);
        $searchQuery = 'foobar';

        $viewModel = new ReviewsViewModel($repository, $paginator, $paginatorVm, $searchQuery, '', $timeline);

        static::assertSame($reviews, $viewModel->getReviews());
    }

    public function testGetAuthors(): void
    {
        $reviews    = [new CodeReview()];
        $repository = new Repository();
        $paginator  = static::createStub(Paginator::class);
        $paginator->method('getIterator')->willReturn(new ArrayIterator($reviews));
        $paginatorVm = static::createStub(PaginatorViewModel::class);
        $timeline    = static::createStub(TimelineViewModel::class);
        $viewModel   = new ReviewsViewModel($repository, $paginator, $paginatorVm, '', '', $timeline);

        $revisionA = new Revision();
        $revisionB = new Revision();
        $revisionC = new Revision();

        $revisionA->setAuthorName('Sherlock');
        $revisionB->setAuthorName('Sherlock');
        $revisionC->setAuthorName('Watson');

        static::assertSame(['Sherlock', 'Watson'], array_values($viewModel->getAuthors(new ArrayCollection([$revisionA, $revisionB, $revisionC]))));
    }
}

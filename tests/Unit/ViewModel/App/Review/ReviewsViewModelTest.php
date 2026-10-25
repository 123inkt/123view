<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Review;

use Doctrine\Common\Collections\ArrayCollection;
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
    public function testGetAuthors(): void
    {
        $reviews     = [new CodeReview()];
        $repository  = new Repository();
        $paginatorVm = static::createStub(PaginatorViewModel::class);
        $timeline    = static::createStub(TimelineViewModel::class);
        $viewModel   = new ReviewsViewModel($repository, $reviews, $paginatorVm, '', '', $timeline);

        $revisionA = new Revision();
        $revisionB = new Revision();
        $revisionC = new Revision();

        $revisionA->setAuthorName('Sherlock');
        $revisionB->setAuthorName('Sherlock');
        $revisionC->setAuthorName('Watson');

        static::assertSame(['Sherlock', 'Watson'], array_values($viewModel->getAuthors(new ArrayCollection([$revisionA, $revisionB, $revisionC]))));
    }
}

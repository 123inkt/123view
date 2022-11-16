<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModel\App\Review;

use ArrayIterator;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\ViewModel\App\Review\PaginatorViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\RevisionsViewModel;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ViewModel\App\Review\RevisionsViewModel
 * @covers ::__construct
 */
class RevisionsViewModelTest extends AbstractTestCase
{
    /**
     * @covers ::getRevisions
     */
    public function testGetRevisions(): void
    {
        $repository         = new Repository();
        $revision           = new Revision();
        $revisions          = $this->createMock(Paginator::class);
        $paginatorViewModel = new PaginatorViewModel($revisions, 5);
        $viewModel          = new RevisionsViewModel($repository, $revisions, $paginatorViewModel, 'search');

        $revisions->expects(self::once())->method('getIterator')->willReturn(new ArrayIterator([$revision]));

        static::assertSame([$revision], $viewModel->getRevisions());
    }
}

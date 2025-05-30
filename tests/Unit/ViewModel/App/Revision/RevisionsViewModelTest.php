<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Revision;

use ArrayIterator;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\PaginatorViewModel;
use DR\Review\ViewModel\App\Revision\RevisionsViewModel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(RevisionsViewModel::class)]
class RevisionsViewModelTest extends AbstractTestCase
{
    public function testGetRevisions(): void
    {
        $repository = new Repository();
        $revision   = new Revision();
        /** @var Paginator<Revision>&MockObject $revisions */
        $revisions = $this->createMock(Paginator::class);
        /** @var PaginatorViewModel<Revision> $paginatorViewModel */
        $paginatorViewModel = new PaginatorViewModel($revisions, 5);
        $viewModel          = new RevisionsViewModel($repository, $revisions, $paginatorViewModel, 'search');

        $revisions->expects($this->once())->method('getIterator')->willReturn(new ArrayIterator([$revision]));

        static::assertSame([$revision], $viewModel->getRevisions());
    }
}

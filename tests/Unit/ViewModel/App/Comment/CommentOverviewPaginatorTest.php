<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Comment;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Comment\CommentOverviewPaginator;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentOverviewPaginator::class)]
class CommentOverviewPaginatorTest extends AbstractTestCase
{
    public function testGetLastPage(): void
    {
        $paginator = new CommentOverviewPaginator(2, 31, 30);

        static::assertSame(2, $paginator->getLastPage());
    }

    public function testGetLastPageEmpty(): void
    {
        $paginator = new CommentOverviewPaginator(1, 0, 30);

        static::assertSame(0, $paginator->getLastPage());
    }
}

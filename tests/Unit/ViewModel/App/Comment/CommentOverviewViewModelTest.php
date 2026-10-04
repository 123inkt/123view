<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Comment;

use DR\Review\Entity\Review\Comment;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Comment\CommentOverviewItem;
use DR\Review\ViewModel\App\Comment\CommentOverviewPaginator;
use DR\Review\ViewModel\App\Comment\CommentOverviewViewModel;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentOverviewViewModel::class)]
class CommentOverviewViewModelTest extends AbstractTestCase
{
    public function testConstructor(): void
    {
        $item      = new CommentOverviewItem(new Comment());
        $paginator = new CommentOverviewPaginator(1, 1, 30);
        $viewModel = new CommentOverviewViewModel([$item], $paginator, 'search', 'create-timestamp');

        static::assertSame([$item], $viewModel->items);
        static::assertSame($paginator, $viewModel->paginator);
        static::assertSame('search', $viewModel->searchQuery);
        static::assertSame('create-timestamp', $viewModel->orderBy);
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Comment;

use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Entity\Review\Comment;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Comment\CommentsViewModel;

/**
 * @coversDefaultClass \DR\Review\ViewModel\App\Comment\CommentsViewModel
 * @covers ::__construct
 */
class CommentsViewModelTest extends AbstractTestCase
{
    /**
     * @covers ::__construct
     */
    public function testGetDetachedComments(): void
    {
        $comment   = new Comment();
        $viewModel = new CommentsViewModel([], [$comment]);
        static::assertSame([$comment], $viewModel->detachedComments);
    }

    /**
     * @covers ::getComments
     */
    public function testGetComments(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineB = new DiffLine(DiffLine::STATE_CHANGED, []);

        $comment  = new Comment();
        $comments = [spl_object_hash($lineA) => [$comment]];

        $viewModel = new CommentsViewModel($comments, []);
        static::assertSame([$comment], $viewModel->getComments($lineA));
        static::assertSame([], $viewModel->getComments($lineB));
    }
}

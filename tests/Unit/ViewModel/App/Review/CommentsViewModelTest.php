<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModel\App\Review;

use DR\GitCommitNotification\Entity\Git\Diff\DiffLine;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\ViewModel\App\Review\CommentsViewModel;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ViewModel\App\Review\CommentsViewModel
 * @covers ::__construct
 */
class CommentsViewModelTest extends AbstractTestCase
{
    /**
     * @covers ::getDetachedComments
     */
    public function testGetDetachedComments(): void
    {
        $comment   = new Comment();
        $viewModel = new CommentsViewModel([], [$comment], []);
        static::assertSame([$comment], $viewModel->getDetachedComments());
    }

    /**
     * @covers ::getComments
     */
    public function testGetComments(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineB = new DiffLine(DiffLine::STATE_CHANGED, []);

        $lineReference = 'foobar:1:2:3';

        $comment  = new Comment();
        $comments = [$lineReference => [$comment]];
        $lines    = [spl_object_hash($lineA) => $lineReference];

        $viewModel = new CommentsViewModel($comments, [], $lines);
        static::assertSame([$comment], $viewModel->getComments($lineA));
        static::assertSame([], $viewModel->getComments($lineB));
    }
}

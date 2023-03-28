<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Comment;

use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentVisibility;
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
        $viewModel = new CommentsViewModel([], [$comment], DiffComparePolicy::IGNORE, CommentVisibility::ALL);
        static::assertSame([$comment], $viewModel->detachedComments);
    }

    /**
     * @covers ::isCommentVisible
     */
    public function testIsCommentVisibleAll(): void
    {
        $commentA = (new Comment())->setState(CommentStateType::OPEN);
        $commentB = (new Comment())->setState(CommentStateType::RESOLVED);

        $viewModel = new CommentsViewModel([], [], DiffComparePolicy::IGNORE, CommentVisibility::ALL);
        static::assertTrue($viewModel->isCommentVisible($commentA));
        static::assertTrue($viewModel->isCommentVisible($commentB));
    }

    /**
     * @covers ::isCommentVisible
     */
    public function testIsCommentVisibleUnresolvedOnly(): void
    {
        $commentA = (new Comment())->setState(CommentStateType::OPEN);
        $commentB = (new Comment())->setState(CommentStateType::RESOLVED);

        $viewModel = new CommentsViewModel([], [], DiffComparePolicy::IGNORE, CommentVisibility::UNRESOLVED);
        static::assertTrue($viewModel->isCommentVisible($commentA));
        static::assertFalse($viewModel->isCommentVisible($commentB));
    }

    /**
     * @covers ::isCommentVisible
     */
    public function testIsCommentVisibleNone(): void
    {
        $commentA = (new Comment())->setState(CommentStateType::OPEN);
        $commentB = (new Comment())->setState(CommentStateType::RESOLVED);

        $viewModel = new CommentsViewModel([], [], DiffComparePolicy::IGNORE, CommentVisibility::NONE);
        static::assertFalse($viewModel->isCommentVisible($commentA));
        static::assertFalse($viewModel->isCommentVisible($commentB));
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

        $viewModel = new CommentsViewModel($comments, [], DiffComparePolicy::IGNORE, CommentVisibility::ALL);
        static::assertSame([$comment], $viewModel->getComments($lineA));
        static::assertSame([], $viewModel->getComments($lineB));
    }
}

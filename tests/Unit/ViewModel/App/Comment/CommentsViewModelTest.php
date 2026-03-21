<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Comment;

use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentVisibilityEnum;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Comment\CommentsViewModel;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentsViewModel::class)]
class CommentsViewModelTest extends AbstractTestCase
{
    public function testGetDetachedComments(): void
    {
        $comment   = new Comment();
        $viewModel = new CommentsViewModel([], [$comment], DiffComparePolicy::IGNORE, CommentVisibilityEnum::ALL);
        static::assertSame([$comment], $viewModel->detachedComments);
    }

    public function testIsCommentVisibleAll(): void
    {
        $commentA = (new Comment())->setState(CommentStateType::OPEN);
        $commentB = (new Comment())->setState(CommentStateType::RESOLVED);

        $viewModel = new CommentsViewModel([], [], DiffComparePolicy::IGNORE, CommentVisibilityEnum::ALL);
        static::assertTrue($viewModel->isCommentVisible($commentA));
        static::assertTrue($viewModel->isCommentVisible($commentB));
    }

    public function testIsCommentVisibleUnresolvedOnly(): void
    {
        $commentA = (new Comment())->setState(CommentStateType::OPEN);
        $commentB = (new Comment())->setState(CommentStateType::RESOLVED);

        $viewModel = new CommentsViewModel([], [], DiffComparePolicy::IGNORE, CommentVisibilityEnum::UNRESOLVED);
        static::assertTrue($viewModel->isCommentVisible($commentA));
        static::assertFalse($viewModel->isCommentVisible($commentB));
    }

    public function testIsCommentVisibleNone(): void
    {
        $commentA = (new Comment())->setState(CommentStateType::OPEN);
        $commentB = (new Comment())->setState(CommentStateType::RESOLVED);

        $viewModel = new CommentsViewModel([], [], DiffComparePolicy::IGNORE, CommentVisibilityEnum::NONE);
        static::assertFalse($viewModel->isCommentVisible($commentA));
        static::assertFalse($viewModel->isCommentVisible($commentB));
    }

    public function testGetComments(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineB = new DiffLine(DiffLine::STATE_CHANGED, []);

        $comment  = new Comment();
        $comments = [spl_object_hash($lineA) => [$comment]];

        $viewModel = new CommentsViewModel($comments, [], DiffComparePolicy::IGNORE, CommentVisibilityEnum::ALL);
        static::assertSame([$comment], $viewModel->getComments($lineA));
        static::assertSame([], $viewModel->getComments($lineB));
    }
}

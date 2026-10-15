<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModelProvider\UserCommentsViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(UserCommentsViewModelProvider::class)]
class UserCommentsViewModelProviderTest extends AbstractTestCase
{
    private CommentRepository&MockObject $commentRepository;
    private UserCommentsViewModelProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->provider          = new UserCommentsViewModelProvider($this->commentRepository);
    }

    public function testGetUserCommentsViewModel(): void
    {
        $user         = new User();
        $comment      = new Comment();
        $otherComment = new Comment();
        $reply        = new CommentReply();
        $reply->setComment($otherComment);

        $this->commentRepository
            ->expects($this->once())
            ->method('findCommentsAndRepliesByUser')
            ->with($user, 'search', CommentRepository::ORDER_UPDATE_TIMESTAMP, 2, 30)
            ->willReturn([$reply, $comment]);
        $this->commentRepository
            ->expects($this->once())
            ->method('countCommentsAndRepliesByUser')
            ->with($user, 'search')
            ->willReturn(61);

        $viewModel = $this->provider->getUserCommentsViewModel($user, 'search', CommentRepository::ORDER_UPDATE_TIMESTAMP, 2);

        static::assertCount(2, $viewModel->comments);
        static::assertSame($otherComment, $viewModel->comments[0]->comment);
        static::assertSame($reply, $viewModel->comments[0]->reply);
        static::assertSame($comment, $viewModel->comments[1]->comment);
        static::assertNull($viewModel->comments[1]->reply);
        static::assertSame('search', $viewModel->searchQuery);
        static::assertSame(CommentRepository::ORDER_UPDATE_TIMESTAMP, $viewModel->orderBy);
        static::assertSame(2, $viewModel->page);
        static::assertSame(3, $viewModel->lastPage);
    }

    public function testGetUserCommentsViewModelEmpty(): void
    {
        $user = new User();

        $this->commentRepository
            ->expects($this->once())
            ->method('findCommentsAndRepliesByUser')
            ->with($user, '', CommentRepository::ORDER_CREATE_TIMESTAMP, 1, 30)
            ->willReturn([]);
        $this->commentRepository
            ->expects($this->once())
            ->method('countCommentsAndRepliesByUser')
            ->with($user, '')
            ->willReturn(0);

        $viewModel = $this->provider->getUserCommentsViewModel($user, '', CommentRepository::ORDER_CREATE_TIMESTAMP, 1);

        static::assertSame([], $viewModel->comments);
        static::assertSame(1, $viewModel->page);
        static::assertSame(0, $viewModel->lastPage);
    }
}

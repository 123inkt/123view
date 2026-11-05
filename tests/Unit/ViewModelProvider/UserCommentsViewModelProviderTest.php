<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\Review\CommentStateEnum;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\UserCommentRepository;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModelProvider\UserCommentsViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(UserCommentsViewModelProvider::class)]
class UserCommentsViewModelProviderTest extends AbstractTestCase
{
    private UserCommentRepository&MockObject $userCommentRepository;
    private UserCommentsViewModelProvider    $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userCommentRepository = $this->createMock(UserCommentRepository::class);
        $this->provider              = new UserCommentsViewModelProvider($this->userCommentRepository);
    }

    #[DataProvider('statesProvider')]
    public function testGetUserCommentsViewModel(?CommentStateEnum $state): void
    {
        $user         = new User();
        $comment      = new Comment();
        $otherComment = new Comment();
        $reply        = new CommentReply();
        $reply->setComment($otherComment);

        $this->userCommentRepository
            ->expects($this->once())
            ->method('findCommentsAndRepliesByUser')
            ->with($user, 'search', UserCommentRepository::ORDER_UPDATE_TIMESTAMP, 2, 30, $state)
            ->willReturn([$reply, $comment]);
        $this->userCommentRepository
            ->expects($this->once())
            ->method('countCommentsAndRepliesByUser')
            ->with($user, 'search', $state)
            ->willReturn(61);

        $viewModel = $this->provider->getUserCommentsViewModel($user, 'search', UserCommentRepository::ORDER_UPDATE_TIMESTAMP, 2, $state);

        static::assertCount(2, $viewModel->comments);
        static::assertSame($otherComment, $viewModel->comments[0]->comment);
        static::assertSame($reply, $viewModel->comments[0]->reply);
        static::assertSame($comment, $viewModel->comments[1]->comment);
        static::assertNull($viewModel->comments[1]->reply);
        static::assertSame('search', $viewModel->searchQuery);
        static::assertSame(UserCommentRepository::ORDER_UPDATE_TIMESTAMP, $viewModel->orderBy);
        static::assertSame($state, $viewModel->state);
        static::assertSame(2, $viewModel->page);
        static::assertSame(3, $viewModel->lastPage);
    }

    /**
     * @return array<string, array{?CommentStateEnum}>
     */
    public static function statesProvider(): array
    {
        return [
            'any'        => [null],
            'resolved'   => [CommentStateEnum::Resolved],
            'unresolved' => [CommentStateEnum::Open],
        ];
    }

    public function testGetUserCommentsViewModelEmpty(): void
    {
        $user = new User();

        $this->userCommentRepository
            ->expects($this->once())
            ->method('findCommentsAndRepliesByUser')
            ->with($user, '', UserCommentRepository::ORDER_CREATE_TIMESTAMP, 1, 30, null)
            ->willReturn([]);
        $this->userCommentRepository
            ->expects($this->once())
            ->method('countCommentsAndRepliesByUser')
            ->with($user, '', null)
            ->willReturn(0);

        $viewModel = $this->provider->getUserCommentsViewModel($user, '', UserCommentRepository::ORDER_CREATE_TIMESTAMP, 1);

        static::assertSame([], $viewModel->comments);
        static::assertNull($viewModel->state);
        static::assertSame(1, $viewModel->page);
        static::assertSame(0, $viewModel->lastPage);
    }
}

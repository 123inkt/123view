<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CommentOverviewRepository;
use DR\Review\Request\Comment\CommentOverviewRequest;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Comment\CommentOverviewItem;
use DR\Review\ViewModelProvider\CommentOverviewViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CommentOverviewViewModelProvider::class)]
class CommentOverviewViewModelProviderTest extends AbstractTestCase
{
    private CommentOverviewRepository&MockObject $repository;
    private CommentOverviewViewModelProvider      $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(CommentOverviewRepository::class);
        $this->provider   = new CommentOverviewViewModelProvider($this->repository);
    }

    public function testGetCommentOverviewViewModel(): void
    {
        $user    = new User();
        $request = static::createStub(CommentOverviewRequest::class);
        $request->method('getPage')->willReturn(2);
        $request->method('getSearchQuery')->willReturn('search');
        $request->method('getOrderBy')->willReturn(CommentOverviewRepository::ORDER_UPDATE_TIMESTAMP);

        $comment = new Comment();
        $reply   = new CommentReply();
        $reply->setComment($comment);
        $items = [new CommentOverviewItem($comment), new CommentOverviewItem($reply)];

        $this->repository
            ->expects($this->once())
            ->method('getByUser')
            ->with($user, 2, 'search', CommentOverviewRepository::ORDER_UPDATE_TIMESTAMP)
            ->willReturn(['items' => $items, 'total' => 31]);

        $viewModel = $this->provider->getCommentOverviewViewModel($user, $request);

        static::assertSame($items, $viewModel->items);
        static::assertSame('search', $viewModel->searchQuery);
        static::assertSame(CommentOverviewRepository::ORDER_UPDATE_TIMESTAMP, $viewModel->orderBy);
        static::assertSame(2, $viewModel->paginator->page);
        static::assertSame(31, $viewModel->paginator->total);
        static::assertSame(2, $viewModel->paginator->getLastPage());
    }
}

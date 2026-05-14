<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use Doctrine\ORM\Tools\Pagination\Paginator;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModelProvider\DraftCommentViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(DraftCommentViewModelProvider::class)]
class DraftCommentViewModelProviderTest extends AbstractTestCase
{
    private CommentRepository&MockObject $commentRepository;
    private DraftCommentViewModelProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->provider          = new DraftCommentViewModelProvider($this->commentRepository);
    }

    public function testGetDraftCommentsViewModel(): void
    {
        $user = new User();

        $reviewA = new CodeReview();
        $reviewA->setId(1);
        $reviewB = new CodeReview();
        $reviewB->setId(2);

        $commentA = new Comment();
        $commentA->setId(10);
        $commentA->setReview($reviewA);

        $commentB = new Comment();
        $commentB->setId(20);
        $commentB->setReview($reviewA);

        $commentC = new Comment();
        $commentC->setId(30);
        $commentC->setReview($reviewB);

        /** @var Paginator<Comment>&MockObject $paginator */
        $paginator = $this->createMock(Paginator::class);
        $paginator->expects($this->once())->method('getIterator')->willReturn(new \ArrayIterator([$commentA, $commentB, $commentC]));

        $this->commentRepository
            ->expects($this->once())
            ->method('getDraftsByUser')
            ->with($user, 2, 30)
            ->willReturn($paginator);

        $viewModel = $this->provider->getDraftCommentsViewModel($user, 2);

        static::assertSame([1 => [10 => $commentA, 20 => $commentB], 2 => [30 => $commentC]], $viewModel->comments);
        static::assertSame([1 => $reviewA, 2 => $reviewB], $viewModel->reviews);
        static::assertSame(2, $viewModel->paginator->page);
    }

    public function testGetDraftCommentsViewModelEmpty(): void
    {
        $user = new User();

        /** @var Paginator<Comment>&MockObject $paginator */
        $paginator = $this->createMock(Paginator::class);
        $paginator->expects($this->once())->method('getIterator')->willReturn(new \ArrayIterator([]));

        $this->commentRepository
            ->expects($this->once())
            ->method('getDraftsByUser')
            ->with($user, 1, 30)
            ->willReturn($paginator);

        $viewModel = $this->provider->getDraftCommentsViewModel($user, 1);

        static::assertSame([], $viewModel->comments);
        static::assertSame([], $viewModel->reviews);
        static::assertSame(1, $viewModel->paginator->page);
    }
}

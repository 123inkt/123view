<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview\Comment;

use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Exception\Ai\CommentNotFoundException;
use DR\Review\Exception\Ai\CommentNotInReviewException;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\CodeReview\Comment\ResolveCommentService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(ResolveCommentService::class)]
class ResolveCommentServiceTest extends AbstractTestCase
{
    private CommentRepository&MockObject $commentRepository;
    private ResolveCommentService        $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->service           = new ResolveCommentService($this->commentRepository);
    }

    public function testResolveThrowsWhenCommentNotFound(): void
    {
        $this->commentRepository->expects($this->once())->method('find')->with(456)->willReturn(null);
        $this->commentRepository->expects($this->never())->method('save');

        $this->expectException(CommentNotFoundException::class);
        $this->service->resolve(456, 123);
    }

    public function testResolveThrowsWhenCommentNotInReview(): void
    {
        $review  = new CodeReview()->setId(999);
        $comment = new Comment()->setId(456)->setReview($review);

        $this->commentRepository->expects($this->once())->method('find')->with(456)->willReturn($comment);
        $this->commentRepository->expects($this->never())->method('save');

        $this->expectException(CommentNotInReviewException::class);
        $this->service->resolve(456, 123);
    }

    public function testResolveAlreadyResolved(): void
    {
        $review  = new CodeReview()->setId(123);
        $comment = new Comment()->setId(456)->setReview($review)->setState(CommentStateType::RESOLVED);

        $this->commentRepository->expects($this->once())->method('find')->with(456)->willReturn($comment);
        $this->commentRepository->expects($this->never())->method('save');

        $result = $this->service->resolve(456, 123);
        static::assertSame('Comment 456 is already resolved.', $result);
    }

    public function testResolveSetsStateAndFlushes(): void
    {
        $review  = new CodeReview()->setId(123);
        $comment = new Comment()->setId(456)->setReview($review)->setState(CommentStateType::OPEN);

        $this->commentRepository->expects($this->once())->method('find')->with(456)->willReturn($comment);
        $this->commentRepository->expects($this->once())->method('save')->with($comment, true);

        $result = $this->service->resolve(456, 123);
        static::assertSame('Comment 456 resolved.', $result);
        static::assertSame(CommentStateType::RESOLVED, $comment->getState());
    }
}

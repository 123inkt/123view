<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai\Mcp;

use DR\Review\Entity\Review\Comment;
use DR\Review\Exception\Ai\CommentNotFoundException;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Security\Voter\CommentVoter;
use DR\Review\Service\Ai\Mcp\DeleteCommentTool;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

#[CoversClass(DeleteCommentTool::class)]
class DeleteCommentToolTest extends AbstractTestCase
{
    private CommentRepository&MockObject $commentRepository;
    private Security&MockObject          $security;
    private DeleteCommentTool            $tool;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->security          = $this->createMock(Security::class);
        $this->tool              = new DeleteCommentTool($this->commentRepository, $this->security);
    }

    public function testInvokeThrowsWhenCommentNotFound(): void
    {
        $this->commentRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->security->expects($this->never())->method('isGranted');

        $this->expectException(CommentNotFoundException::class);
        ($this->tool)(123);
    }

    public function testInvokeThrowsWhenUserCannotDeleteComment(): void
    {
        $comment = new Comment()->setId(123);
        $this->commentRepository->expects($this->once())->method('find')->with(123)->willReturn($comment);
        $this->security->expects($this->once())->method('isGranted')->with(CommentVoter::DELETE, $comment)->willReturn(false);
        $this->commentRepository->expects($this->never())->method('remove');

        $this->expectException(AccessDeniedHttpException::class);
        ($this->tool)(123);
    }

    public function testInvokeDeletesComment(): void
    {
        $comment = new Comment()->setId(123);
        $this->commentRepository->expects($this->once())->method('find')->with(123)->willReturn($comment);
        $this->security->expects($this->once())->method('isGranted')->with(CommentVoter::DELETE, $comment)->willReturn(true);
        $this->commentRepository->expects($this->once())->method('remove')->with($comment, true);

        static::assertSame('Comment deleted successfully', ($this->tool)(123));
    }
}

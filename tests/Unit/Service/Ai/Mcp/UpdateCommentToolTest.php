<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai\Mcp;

use DR\Review\Entity\Review\Comment;
use DR\Review\Exception\Ai\CommentNotFoundException;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Security\Voter\CommentVoter;
use DR\Review\Service\Ai\Mcp\UpdateCommentTool;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

#[CoversClass(UpdateCommentTool::class)]
class UpdateCommentToolTest extends AbstractTestCase
{
    private CommentRepository&MockObject $commentRepository;
    private Security&MockObject          $security;
    private UpdateCommentTool             $tool;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->security          = $this->createMock(Security::class);
        $this->tool              = new UpdateCommentTool($this->commentRepository, $this->security);
    }

    public function testInvokeThrowsWhenCommentNotFound(): void
    {
        $this->commentRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->security->expects($this->never())->method('isGranted');

        $this->expectException(CommentNotFoundException::class);
        ($this->tool)(123, 'Updated message');
    }

    public function testInvokeThrowsWhenUserCannotEditComment(): void
    {
        $comment = new Comment()->setId(123)->setMessage('Original message');
        $this->commentRepository->expects($this->once())->method('find')->with(123)->willReturn($comment);
        $this->security->expects($this->once())->method('isGranted')->with(CommentVoter::EDIT, $comment)->willReturn(false);
        $this->commentRepository->expects($this->never())->method('save');

        $this->expectException(AccessDeniedHttpException::class);
        ($this->tool)(123, 'Updated message');
    }

    public function testInvokeUpdatesComment(): void
    {
        $comment = new Comment()->setId(123)->setMessage('Original message');
        $this->commentRepository->expects($this->once())->method('find')->with(123)->willReturn($comment);
        $this->security->expects($this->once())->method('isGranted')->with(CommentVoter::EDIT, $comment)->willReturn(true);
        $this->commentRepository->expects($this->once())->method('save')->with($comment, true);

        $result = ($this->tool)(123, 'Updated message');

        static::assertSame('Comment updated', $result);
        static::assertSame('Updated message', $comment->getMessage());
    }
}

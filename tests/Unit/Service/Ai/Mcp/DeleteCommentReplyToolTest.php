<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai\Mcp;

use DR\Review\Entity\Review\CommentReply;
use DR\Review\Exception\Ai\CommentReplyNotFoundException;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Security\Voter\CommentReplyVoter;
use DR\Review\Service\Ai\Mcp\DeleteCommentReplyTool;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

#[CoversClass(DeleteCommentReplyTool::class)]
class DeleteCommentReplyToolTest extends AbstractTestCase
{
    private CommentReplyRepository&MockObject $commentReplyRepository;
    private Security&MockObject               $security;
    private DeleteCommentReplyTool             $tool;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commentReplyRepository = $this->createMock(CommentReplyRepository::class);
        $this->security               = $this->createMock(Security::class);
        $this->tool                   = new DeleteCommentReplyTool($this->commentReplyRepository, $this->security);
    }

    public function testInvokeThrowsWhenReplyNotFound(): void
    {
        $this->commentReplyRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->security->expects($this->never())->method('isGranted');

        $this->expectException(CommentReplyNotFoundException::class);
        $this->expectExceptionMessage('Comment reply not found: 123');
        ($this->tool)(123);
    }

    public function testInvokeThrowsWhenUserCannotDeleteReply(): void
    {
        $reply = new CommentReply()->setId(123);
        $this->commentReplyRepository->expects($this->once())->method('find')->with(123)->willReturn($reply);
        $this->security->expects($this->once())->method('isGranted')->with(CommentReplyVoter::DELETE, $reply)->willReturn(false);
        $this->commentReplyRepository->expects($this->never())->method('remove');

        $this->expectException(AccessDeniedHttpException::class);
        ($this->tool)(123);
    }

    public function testInvokeDeletesReply(): void
    {
        $reply = new CommentReply()->setId(123);
        $this->commentReplyRepository->expects($this->once())->method('find')->with(123)->willReturn($reply);
        $this->security->expects($this->once())->method('isGranted')->with(CommentReplyVoter::DELETE, $reply)->willReturn(true);
        $this->commentReplyRepository->expects($this->once())->method('remove')->with($reply, true);

        static::assertSame('Comment reply deleted', ($this->tool)(123));
    }
}

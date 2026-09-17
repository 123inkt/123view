<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai\Mcp;

use DR\Review\Entity\Review\CommentReply;
use DR\Review\Exception\Ai\CommentNotFoundException;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Security\Voter\CommentReplyVoter;
use DR\Review\Service\Ai\Mcp\UpdateCommentReplyTool;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

#[CoversClass(UpdateCommentReplyTool::class)]
class UpdateCommentReplyToolTest extends AbstractTestCase
{
    private CommentReplyRepository&MockObject $commentReplyRepository;
    private Security&MockObject               $security;
    private UpdateCommentReplyTool             $tool;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commentReplyRepository = $this->createMock(CommentReplyRepository::class);
        $this->security               = $this->createMock(Security::class);
        $this->tool                   = new UpdateCommentReplyTool($this->commentReplyRepository, $this->security);
    }

    public function testInvokeThrowsWhenReplyNotFound(): void
    {
        $this->commentReplyRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->security->expects($this->never())->method('isGranted');

        $this->expectException(CommentNotFoundException::class);
        ($this->tool)(123, 'Updated reply');
    }

    public function testInvokeThrowsWhenUserCannotEditReply(): void
    {
        $reply = new CommentReply()->setId(123);
        $reply->setMessage('Original reply');
        $this->commentReplyRepository->expects($this->once())->method('find')->with(123)->willReturn($reply);
        $this->security->expects($this->once())->method('isGranted')->with(CommentReplyVoter::EDIT, $reply)->willReturn(false);
        $this->commentReplyRepository->expects($this->never())->method('save');

        $this->expectException(AccessDeniedHttpException::class);
        ($this->tool)(123, 'Updated reply');
    }

    public function testInvokeUpdatesReply(): void
    {
        $reply = new CommentReply()->setId(123);
        $reply->setMessage('Original reply');
        $this->commentReplyRepository->expects($this->once())->method('find')->with(123)->willReturn($reply);
        $this->security->expects($this->once())->method('isGranted')->with(CommentReplyVoter::EDIT, $reply)->willReturn(true);
        $this->commentReplyRepository->expects($this->once())->method('save')->with($reply, true);

        $result = ($this->tool)(123, 'Updated reply');

        static::assertSame('Comment reply updated', $result);
        static::assertSame('Updated reply', $reply->getMessage());
    }
}

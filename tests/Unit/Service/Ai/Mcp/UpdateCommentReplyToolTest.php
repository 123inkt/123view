<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai\Mcp;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\User\User;
use DR\Review\Exception\Ai\CommentReplyNotFoundException;
use DR\Review\Message\Comment\CommentReplyUpdated;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Security\Voter\CommentReplyVoter;
use DR\Review\Service\Ai\Mcp\UpdateCommentReplyTool;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(UpdateCommentReplyTool::class)]
class UpdateCommentReplyToolTest extends AbstractTestCase
{
    private CommentReplyRepository&MockObject $commentReplyRepository;
    private MessageBusInterface&MockObject    $bus;
    private Security&MockObject               $security;
    private UpdateCommentReplyTool             $tool;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commentReplyRepository = $this->createMock(CommentReplyRepository::class);
        $this->bus                    = $this->createMock(MessageBusInterface::class);
        $this->security               = $this->createMock(Security::class);
        $this->tool                   = new UpdateCommentReplyTool($this->commentReplyRepository, $this->bus, $this->security);
    }

    public function testInvokeThrowsWhenReplyNotFound(): void
    {
        $this->commentReplyRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->security->expects($this->never())->method('isGranted');
        $this->bus->expects($this->never())->method('dispatch');

        $this->expectException(CommentReplyNotFoundException::class);
        $this->expectExceptionMessage('Comment reply not found: 123');
        ($this->tool)(123, 'Updated reply');
    }

    public function testInvokeThrowsWhenUserCannotEditReply(): void
    {
        $reply = new CommentReply()->setId(123);
        $reply->setMessage('Original reply');
        $this->commentReplyRepository->expects($this->once())->method('find')->with(123)->willReturn($reply);
        $this->security->expects($this->once())->method('isGranted')->with(CommentReplyVoter::EDIT, $reply)->willReturn(false);
        $this->commentReplyRepository->expects($this->never())->method('save');
        $this->bus->expects($this->never())->method('dispatch');

        $this->expectException(AccessDeniedHttpException::class);
        ($this->tool)(123, 'Updated reply');
    }

    public function testInvokeUpdatesReply(): void
    {
        $review = new CodeReview()->setId(456);
        $comment = new Comment()->setReview($review);
        $reply = new CommentReply()->setId(123);
        $reply->setMessage('Original reply');
        $reply->setComment($comment);
        $user = new User()->setId(789);

        $this->commentReplyRepository->expects($this->once())->method('find')->with(123)->willReturn($reply);
        $this->security->expects($this->once())->method('isGranted')->with(CommentReplyVoter::EDIT, $reply)->willReturn(true);
        $this->security->expects($this->once())->method('getUser')->willReturn($user);
        $this->commentReplyRepository->expects($this->once())->method('save')->with($reply, true);
        $this->bus
            ->expects($this->once())
            ->method('dispatch')
            ->with(new CommentReplyUpdated(456, 123, 789, 'Original reply'))
            ->willReturn($this->envelope);

        $result = ($this->tool)(123, 'Updated reply');

        static::assertSame('Comment reply updated', $result);
        static::assertSame('Updated reply', $reply->getMessage());
        static::assertEqualsWithDelta(time(), $reply->getUpdateTimestamp(), 10);
    }
}

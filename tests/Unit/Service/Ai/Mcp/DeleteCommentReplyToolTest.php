<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai\Mcp;

use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\User\User;
use DR\Review\Exception\Ai\CommentReplyNotFoundException;
use DR\Review\Message\Comment\CommentReplyRemoved;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Security\Voter\CommentReplyVoter;
use DR\Review\Service\Ai\Mcp\DeleteCommentReplyTool;
use DR\Review\Service\CodeReview\Comment\CommentEventMessageFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(DeleteCommentReplyTool::class)]
class DeleteCommentReplyToolTest extends AbstractTestCase
{
    private CommentReplyRepository&MockObject     $commentReplyRepository;
    private CommentEventMessageFactory&MockObject $messageFactory;
    private MessageBusInterface&MockObject        $bus;
    private Security&MockObject                    $security;
    private DeleteCommentReplyTool                  $tool;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commentReplyRepository = $this->createMock(CommentReplyRepository::class);
        $this->messageFactory         = $this->createMock(CommentEventMessageFactory::class);
        $this->bus                    = $this->createMock(MessageBusInterface::class);
        $this->security               = $this->createMock(Security::class);
        $this->tool                   = new DeleteCommentReplyTool($this->commentReplyRepository, $this->messageFactory, $this->bus, $this->security);
    }

    public function testInvokeThrowsWhenReplyNotFound(): void
    {
        $this->commentReplyRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->security->expects($this->never())->method('isGranted');
        $this->messageFactory->expects($this->never())->method('createReplyRemoved');
        $this->bus->expects($this->never())->method('dispatch');

        $this->expectException(CommentReplyNotFoundException::class);
        $this->expectExceptionMessage('Comment reply not found: 123');
        ($this->tool)(123);
    }

    public function testInvokeDeleteDenied(): void
    {
        $reply = new CommentReply()->setId(123);
        $this->commentReplyRepository->expects($this->once())->method('find')->with(123)->willReturn($reply);
        $this->security->expects($this->once())->method('isGranted')->with(CommentReplyVoter::DELETE, $reply)->willReturn(false);
        $this->commentReplyRepository->expects($this->never())->method('remove');
        $this->messageFactory->expects($this->never())->method('createReplyRemoved');
        $this->bus->expects($this->never())->method('dispatch');

        $this->expectException(AccessDeniedHttpException::class);
        ($this->tool)(123);
    }

    public function testInvokeDeletesReply(): void
    {
        $reply = new CommentReply()->setId(123);
        $user  = new User()->setId(456);
        $event = new CommentReplyRemoved(1, 2, 3, 4, 5, 'message', null);

        $this->commentReplyRepository->expects($this->once())->method('find')->with(123)->willReturn($reply);
        $this->security->expects($this->once())->method('isGranted')->with(CommentReplyVoter::DELETE, $reply)->willReturn(true);
        $this->security->expects($this->once())->method('getUser')->willReturn($user);
        $this->messageFactory->expects($this->once())->method('createReplyRemoved')->with($reply, $user)->willReturn($event);
        $this->commentReplyRepository->expects($this->once())->method('remove')->with($reply, true);
        $this->bus->expects($this->once())->method('dispatch')->with($event)->willReturn($this->envelope);

        static::assertSame('Comment reply deleted', ($this->tool)(123));
    }
}

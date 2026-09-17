<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai\Mcp;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\User\User;
use DR\Review\Exception\Ai\CommentNotFoundException;
use DR\Review\Message\Comment\CommentReplyRemoved;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Security\Voter\CommentVoter;
use DR\Review\Service\Ai\Mcp\DeleteCommentTool;
use DR\Review\Service\CodeReview\Comment\CommentEventMessageFactory;
use DR\Review\Tests\AbstractTestCase;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(DeleteCommentTool::class)]
class DeleteCommentToolTest extends AbstractTestCase
{
    private CommentRepository&MockObject          $commentRepository;
    private CommentEventMessageFactory&MockObject $messageFactory;
    private MessageBusInterface&MockObject        $bus;
    private Security&MockObject                    $security;
    private DeleteCommentTool                       $tool;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->messageFactory   = $this->createMock(CommentEventMessageFactory::class);
        $this->bus              = $this->createMock(MessageBusInterface::class);
        $this->security         = $this->createMock(Security::class);
        $this->tool             = new DeleteCommentTool($this->commentRepository, $this->messageFactory, $this->bus, $this->security);
    }

    public function testInvokeThrowsWhenCommentNotFound(): void
    {
        $this->commentRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->security->expects($this->never())->method('isGranted');
        $this->messageFactory->expects($this->never())->method('createReplyRemoved');
        $this->bus->expects($this->never())->method('dispatch');

        $this->expectException(CommentNotFoundException::class);
        ($this->tool)(123);
    }

    public function testInvokeThrowsWhenUserCannotDeleteComment(): void
    {
        $comment = new Comment()->setId(123);
        $this->commentRepository->expects($this->once())->method('find')->with(123)->willReturn($comment);
        $this->security->expects($this->once())->method('isGranted')->with(CommentVoter::DELETE, $comment)->willReturn(false);
        $this->commentRepository->expects($this->never())->method('remove');
        $this->messageFactory->expects($this->never())->method('createReplyRemoved');
        $this->bus->expects($this->never())->method('dispatch');

        $this->expectException(AccessDeniedHttpException::class);
        ($this->tool)(123);
    }

    public function testInvokeDeletesCommentWithoutReplies(): void
    {
        $comment = new Comment()->setId(123);
        $this->commentRepository->expects($this->once())->method('find')->with(123)->willReturn($comment);
        $this->security->expects($this->once())->method('isGranted')->with(CommentVoter::DELETE, $comment)->willReturn(true);
        $this->security->expects($this->never())->method('getUser');
        $this->messageFactory->expects($this->never())->method('createReplyRemoved');
        $this->commentRepository->expects($this->once())->method('remove')->with($comment, true);
        $this->bus->expects($this->never())->method('dispatch');

        static::assertSame('Comment deleted successfully', ($this->tool)(123));
    }

    public function testInvokeDeletesCommentAndDispatchesReplyRemovalEvents(): void
    {
        $comment = new Comment()->setId(123);
        $replyOne = new CommentReply()->setId(1);
        $replyTwo = new CommentReply()->setId(2);
        $comment->getReplies()->add($replyOne);
        $comment->getReplies()->add($replyTwo);

        $user     = new User()->setId(456);
        $eventOne = new CommentReplyRemoved(1, 2, 3, 4, 5, 'message one', null);
        $eventTwo = new CommentReplyRemoved(6, 7, 8, 9, 10, 'message two', null);
        $this->commentRepository->expects($this->once())->method('find')->with(123)->willReturn($comment);
        $this->security->expects($this->once())->method('isGranted')->with(CommentVoter::DELETE, $comment)->willReturn(true);
        $this->security->expects($this->exactly(2))->method('getUser')->willReturn($user);
        $this->messageFactory
            ->expects($this->exactly(2))
            ->method('createReplyRemoved')
            ->willReturnCallback(
                static function (CommentReply $reply, User $byUser) use ($replyOne, $replyTwo, $user, $eventOne, $eventTwo): CommentReplyRemoved {
                    self::assertSame($user, $byUser);
                    if ($reply === $replyOne) {
                        return $eventOne;
                    }
                    if ($reply === $replyTwo) {
                        return $eventTwo;
                    }

                    throw new LogicException('Unexpected reply');
                }
            );
        $this->commentRepository->expects($this->once())->method('remove')->with($comment, true);
        $this->bus
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->with(
                self::callback(
                    static fn(object $message): bool => $message === $eventOne || $message === $eventTwo
                )
            )
            ->willReturn($this->envelope);

        static::assertSame('Comment deleted successfully', ($this->tool)(123));
    }
}

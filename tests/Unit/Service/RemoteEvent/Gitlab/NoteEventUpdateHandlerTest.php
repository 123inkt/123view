<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\RemoteEvent\Gitlab;

use DR\PHPUnitExtensions\Symfony\ClockTestTrait;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentModificationEnum;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\Review\CommentStateEnum;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentReplyUpdated;
use DR\Review\Model\Api\Gitlab\MergeRequest;
use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\NoteEventHandlerLogger;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEventUpdateHandler;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(NoteEventUpdateHandler::class)]
class NoteEventUpdateHandlerTest extends AbstractTestCase
{
    use ClockTestTrait;

    private NoteEventHandlerLogger&MockObject $eventLogger;
    private CommentRepository&MockObject      $commentRepository;
    private CommentReplyRepository&MockObject $replyRepository;
    private MessageBusInterface&MockObject     $bus;
    private NoteEventUpdateHandler             $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventLogger       = $this->createMock(NoteEventHandlerLogger::class);
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->replyRepository   = $this->createMock(CommentReplyRepository::class);
        $this->bus               = $this->createMock(MessageBusInterface::class);
        $this->handler           = new NoteEventUpdateHandler($this->eventLogger, $this->commentRepository, $this->replyRepository, $this->bus);
    }

    /**
     * @param 'create'|'update'      $action
     * @param 'MergeRequest'|'Issue' $noteType
     */
    #[TestWith([new NoteEvent(), 'update', 'MergeRequest', true])]
    #[TestWith([new NoteEvent(), 'create', 'MergeRequest', false])]
    #[TestWith([new NoteEvent(), 'update', 'Issue', false])]
    #[TestWith([new stdClass(), 'update', 'MergeRequest', false])]
    public function testSupportsUpdateMergeRequestNotes(object $event, string $action, string $noteType, bool $expected): void
    {
        $this->eventLogger->expects($this->never())->method(static::anything());
        $this->commentRepository->expects($this->never())->method(static::anything());
        $this->replyRepository->expects($this->never())->method(static::anything());
        $this->bus->expects($this->never())->method(static::anything());

        if ($event instanceof NoteEvent) {
            $event->action   = $action;
            $event->noteType = $noteType;
        }

        static::assertSame($expected, $this->handler->supports($event));
    }

    public function testHandleSkipsWhenCommentIsNotFound(): void
    {
        $event = $this->createEvent();
        $this->commentRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['extReferenceId' => '7:discussion:42'])
            ->willReturn(null);
        $this->replyRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['extReferenceId' => '7:discussion:42'])
            ->willReturn(null);
        $this->eventLogger->expects($this->once())
            ->method('logCommentNotFound')
            ->with($event, '7:discussion:42');
        $this->commentRepository->expects($this->never())->method('save');
        $this->bus->expects($this->never())->method('dispatch');

        $this->handler->handle($event);
    }

    public function testHandleSkipsUnchangedMessage(): void
    {
        $event   = $this->createEvent();
        $comment = new Comment()->setMessage('Comment')->setUpdateTimestamp(123);
        $this->commentRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['extReferenceId' => '7:discussion:42'])
            ->willReturn($comment);
        $this->eventLogger->expects($this->once())
            ->method('logCommentUnchanged')
            ->with($event, '7:discussion:42');
        $this->commentRepository->expects($this->never())->method('save');
        $this->replyRepository->expects($this->never())->method(static::anything());
        $this->bus->expects($this->never())->method(static::anything());

        $this->handler->handle($event);

        static::assertSame('Comment', $comment->getMessage());
        static::assertSame(123, $comment->getUpdateTimestamp());
    }

    public function testHandleUpdatesCommentMessage(): void
    {
        $event   = $this->createEvent();
        $comment = new Comment()->setMessage('Original comment');
        $this->commentRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['extReferenceId' => '7:discussion:42'])
            ->willReturn($comment);
        $this->commentRepository->expects($this->once())->method('save')->with($comment, true);
        $this->replyRepository->expects($this->never())->method(static::anything());
        $this->bus->expects($this->never())->method(static::anything());
        $this->eventLogger->expects($this->once())
            ->method('logCommentMessageUpdated')
            ->with($event, '7:discussion:42');

        $this->handler->handle($event);

        static::assertSame('Comment', $comment->getMessage());
        static::assertSame(CommentModificationEnum::Gitlab, $comment->getModifiedBy());
        static::assertSame(self::time(), $comment->getUpdateTimestamp());
    }

    public function testHandleResolvesComment(): void
    {
        $event            = $this->createEvent();
        $event->resolvedAt = '2026-09-29T12:00:00.000Z';
        $comment          = new Comment()->setMessage('Comment');
        $this->commentRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['extReferenceId' => '7:discussion:42'])
            ->willReturn($comment);
        $this->commentRepository->expects($this->once())->method('save')->with($comment, true);
        $this->replyRepository->expects($this->never())->method(static::anything());
        $this->bus->expects($this->never())->method(static::anything());
        $this->eventLogger->expects($this->once())
            ->method('logCommentMessageUpdated')
            ->with($event, '7:discussion:42');

        $this->handler->handle($event);

        static::assertSame(CommentStateEnum::Resolved, $comment->getState());
        static::assertSame(CommentModificationEnum::Gitlab, $comment->getModifiedBy());
    }

    public function testHandleUnresolvesComment(): void
    {
        $event   = $this->createEvent();
        $comment = new Comment()->setMessage('Comment')->setState(CommentStateEnum::Resolved);
        $this->commentRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['extReferenceId' => '7:discussion:42'])
            ->willReturn($comment);
        $this->commentRepository->expects($this->once())->method('save')->with($comment, true);
        $this->replyRepository->expects($this->never())->method(static::anything());
        $this->bus->expects($this->never())->method(static::anything());
        $this->eventLogger->expects($this->once())
            ->method('logCommentMessageUpdated')
            ->with($event, '7:discussion:42');

        $this->handler->handle($event);

        static::assertSame(CommentStateEnum::Open, $comment->getState());
        static::assertSame(CommentModificationEnum::Gitlab, $comment->getModifiedBy());
    }

    public function testHandleSkipsUnchangedReplyMessage(): void
    {
        $event = $this->createEvent();
        $reply = new CommentReply();
        $reply->setMessage('Comment');
        $reply->setUpdateTimestamp(123);
        $this->commentRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['extReferenceId' => '7:discussion:42'])
            ->willReturn(null);
        $this->replyRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['extReferenceId' => '7:discussion:42'])
            ->willReturn($reply);
        $this->eventLogger->expects($this->once())
            ->method('logCommentUnchanged')
            ->with($event, '7:discussion:42', true);
        $this->replyRepository->expects($this->never())->method('save');
        $this->bus->expects($this->never())->method(static::anything());

        $this->handler->handle($event);

        static::assertSame('Comment', $reply->getMessage());
        static::assertSame(123, $reply->getUpdateTimestamp());
    }

    public function testHandleUpdatesReplyMessage(): void
    {
        $event   = $this->createEvent();
        $review  = new CodeReview()->setId(456);
        $comment = new Comment()->setReview($review);
        $user    = new User()->setId(789);
        $reply   = new CommentReply()->setId(321);
        $reply->setMessage('Original comment');
        $reply->setComment($comment);
        $reply->setUser($user);
        $this->commentRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['extReferenceId' => '7:discussion:42'])
            ->willReturn(null);
        $this->replyRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['extReferenceId' => '7:discussion:42'])
            ->willReturn($reply);
        $this->replyRepository->expects($this->once())->method('save')->with($reply, true);
        $this->bus->expects($this->once())
            ->method('dispatch')
            ->with(new CommentReplyUpdated(456, 321, 789, 'Original comment', CommentModificationEnum::Gitlab))
            ->willReturn($this->envelope);
        $this->eventLogger->expects($this->once())
            ->method('logCommentMessageUpdated')
            ->with($event, '7:discussion:42', true);

        $this->handler->handle($event);

        static::assertSame('Comment', $reply->getMessage());
        static::assertSame(CommentModificationEnum::Gitlab, $reply->getModifiedBy());
        static::assertSame(self::time(), $reply->getUpdateTimestamp());
    }

    private function createEvent(): NoteEvent
    {
        $event                                = new NoteEvent();
        $event->id                            = 42;
        $event->discussionId                  = 'discussion';
        $event->note                          = 'Comment';
        $event->noteType                      = 'MergeRequest';
        $event->action                        = 'update';
        $event->mergeRequest                  = new MergeRequest();
        $event->mergeRequest->mergeRequestIId = 7;

        return $event;
    }

    protected function freezeTimeAt(): int
    {
        return 1_700_000_000;
    }
}

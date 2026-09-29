<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\RemoteEvent\Gitlab;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Model\Api\Gitlab\MergeRequest;
use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\NoteEventHandlerLogger;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEventCommentUpdateHandler;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEventReplyUpdateHandler;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEventUpdateHandler;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

#[CoversClass(NoteEventUpdateHandler::class)]
class NoteEventUpdateHandlerTest extends AbstractTestCase
{
    private NoteEventHandlerLogger&MockObject        $eventLogger;
    private CommentRepository&MockObject             $commentRepository;
    private CommentReplyRepository&MockObject        $replyRepository;
    private NoteEventCommentUpdateHandler&MockObject $commentUpdateHandler;
    private NoteEventReplyUpdateHandler&MockObject   $replyUpdateHandler;
    private NoteEventUpdateHandler                   $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventLogger       = $this->createMock(NoteEventHandlerLogger::class);
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->replyRepository   = $this->createMock(CommentReplyRepository::class);
        $this->commentUpdateHandler = $this->createMock(NoteEventCommentUpdateHandler::class);
        $this->replyUpdateHandler   = $this->createMock(NoteEventReplyUpdateHandler::class);
        $this->handler           = new NoteEventUpdateHandler(
            $this->eventLogger,
            $this->commentRepository,
            $this->replyRepository,
            $this->commentUpdateHandler,
            $this->replyUpdateHandler,
        );
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
        $this->commentUpdateHandler->expects($this->never())->method(static::anything());
        $this->replyUpdateHandler->expects($this->never())->method(static::anything());

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
        $this->commentUpdateHandler->expects($this->never())->method('handle');
        $this->replyUpdateHandler->expects($this->never())->method('handle');

        $this->handler->handle($event);
    }

    public function testHandleDelegatesCommentUpdate(): void
    {
        $event   = $this->createEvent();
        $comment = new Comment();
        $this->commentRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['extReferenceId' => '7:discussion:42'])
            ->willReturn($comment);
        $this->eventLogger->expects($this->never())->method(static::anything());
        $this->replyRepository->expects($this->never())->method(static::anything());
        $this->commentUpdateHandler->expects($this->once())
            ->method('handle')
            ->with($event, $comment, '7:discussion:42');
        $this->replyUpdateHandler->expects($this->never())->method('handle');

        $this->handler->handle($event);
    }

    public function testHandleDelegatesReplyUpdate(): void
    {
        $event = $this->createEvent();
        $reply = new CommentReply();
        $this->commentRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['extReferenceId' => '7:discussion:42'])
            ->willReturn(null);
        $this->replyRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['extReferenceId' => '7:discussion:42'])
            ->willReturn($reply);
        $this->eventLogger->expects($this->never())->method(static::anything());
        $this->commentUpdateHandler->expects($this->never())->method('handle');
        $this->replyUpdateHandler->expects($this->once())
            ->method('handle')
            ->with($event, $reply, '7:discussion:42');

        $this->handler->handle($event);
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
}

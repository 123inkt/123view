<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\RemoteEvent\Gitlab;

use DR\PHPUnitExtensions\Symfony\ClockTestTrait;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentModificationEnum;
use DR\Review\Model\Api\Gitlab\MergeRequest;
use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\NoteEventHandlerLogger;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEventUpdateHandler;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

#[CoversClass(NoteEventUpdateHandler::class)]
class NoteEventUpdateHandlerTest extends AbstractTestCase
{
    use ClockTestTrait;

    private NoteEventHandlerLogger&MockObject $eventLogger;
    private CommentRepository&MockObject      $commentRepository;
    private NoteEventUpdateHandler             $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventLogger       = $this->createMock(NoteEventHandlerLogger::class);
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->handler           = new NoteEventUpdateHandler($this->eventLogger, $this->commentRepository);
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
        $this->eventLogger->expects($this->once())
            ->method('logCommentNotFound')
            ->with($event, '7:discussion:42');
        $this->commentRepository->expects($this->never())->method('save');

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
        $this->eventLogger->expects($this->once())
            ->method('logCommentUpdated')
            ->with($event, '7:discussion:42');

        $this->handler->handle($event);

        static::assertSame('Comment', $comment->getMessage());
        static::assertSame(CommentModificationEnum::Gitlab, $comment->getModifiedBy());
        static::assertSame(self::time(), $comment->getUpdateTimestamp());
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

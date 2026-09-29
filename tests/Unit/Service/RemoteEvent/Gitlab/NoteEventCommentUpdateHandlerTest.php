<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\RemoteEvent\Gitlab;

use DateTimeImmutable;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentModificationEnum;
use DR\Review\Entity\Review\CommentStateEnum;
use DR\Review\Model\Api\Gitlab\MergeRequest;
use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\Api\Gitlab\GitlabCommentFormatter;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\NoteEventHandlerLogger;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEventCommentUpdateHandler;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(NoteEventCommentUpdateHandler::class)]
class NoteEventCommentUpdateHandlerTest extends AbstractTestCase
{
    private NoteEventHandlerLogger&MockObject $eventLogger;
    private CommentRepository&MockObject      $commentRepository;
    private GitlabCommentFormatter&MockObject $commentFormatter;
    private NoteEventCommentUpdateHandler     $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventLogger       = $this->createMock(NoteEventHandlerLogger::class);
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->commentFormatter  = $this->createMock(GitlabCommentFormatter::class);
        $this->handler           = new NoteEventCommentUpdateHandler(
            $this->eventLogger,
            $this->commentFormatter,
            $this->commentRepository,
        );
    }

    public function testHandleSkipsUnchangedMessage(): void
    {
        $event   = $this->createEvent();
        $comment = new Comment()->setMessage('Comment')->setUpdateTimestamp(new DateTimeImmutable()->setTimestamp(123));
        $this->commentFormatter->expects($this->once())
            ->method('format')
            ->with($comment)
            ->willReturn('Comment');
        $this->eventLogger->expects($this->once())
            ->method('logCommentUnchanged')
            ->with($event, '7:discussion:42');
        $this->commentRepository->expects($this->never())->method('save');

        $this->handler->handle($event, $comment, '7:discussion:42');

        static::assertSame('Comment', $comment->getMessage());
        static::assertSame(123, $comment->getUpdateTimestamp()->getTimestamp());
    }

    public function testHandleUpdatesCommentMessage(): void
    {
        $event   = $this->createEvent();
        $comment = new Comment()->setMessage('Original comment');
        $this->commentFormatter->expects($this->once())
            ->method('format')
            ->with($comment)
            ->willReturn('Original comment');
        $this->commentRepository->expects($this->once())->method('save')->with($comment, true);
        $this->eventLogger->expects($this->once())
            ->method('logCommentMessageUpdated')
            ->with($event, '7:discussion:42');

        $this->handler->handle($event, $comment, '7:discussion:42');

        static::assertSame('Comment', $comment->getMessage());
        static::assertSame(CommentModificationEnum::Gitlab, $comment->getModifiedBy());
    }

    public function testHandleResolvesComment(): void
    {
        $event            = $this->createEvent();
        $event->resolvedAt = '2026-09-29T12:00:00.000Z';
        $comment          = new Comment()->setMessage('Comment');
        $this->commentFormatter->expects($this->once())
            ->method('format')
            ->with($comment)
            ->willReturn('Comment');
        $this->commentRepository->expects($this->once())->method('save')->with($comment, true);
        $this->eventLogger->expects($this->once())
            ->method('logCommentStateUpdated')
            ->with($event, '7:discussion:42', CommentStateEnum::Resolved);

        $this->handler->handle($event, $comment, '7:discussion:42');

        static::assertSame(CommentStateEnum::Resolved, $comment->getState());
        static::assertSame(CommentModificationEnum::Gitlab, $comment->getModifiedBy());
    }

    public function testHandleUnresolvesComment(): void
    {
        $event   = $this->createEvent();
        $comment = new Comment()->setMessage('Comment')->setState(CommentStateEnum::Resolved);
        $this->commentFormatter->expects($this->once())
            ->method('format')
            ->with($comment)
            ->willReturn('Comment');
        $this->commentRepository->expects($this->once())->method('save')->with($comment, true);
        $this->eventLogger->expects($this->once())
            ->method('logCommentStateUpdated')
            ->with($event, '7:discussion:42', CommentStateEnum::Open);

        $this->handler->handle($event, $comment, '7:discussion:42');

        static::assertSame(CommentStateEnum::Open, $comment->getState());
        static::assertSame(CommentModificationEnum::Gitlab, $comment->getModifiedBy());
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

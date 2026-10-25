<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\RemoteEvent\Gitlab;

use DateTimeImmutable;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentModificationEnum;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentReplyUpdated;
use DR\Review\Model\Api\Gitlab\MergeRequest;
use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\NoteEventHandlerLogger;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEventReplyUpdateHandler;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(NoteEventReplyUpdateHandler::class)]
class NoteEventReplyUpdateHandlerTest extends AbstractTestCase
{
    private NoteEventHandlerLogger&MockObject $eventLogger;
    private CommentReplyRepository&MockObject $replyRepository;
    private MessageBusInterface&MockObject     $bus;
    private NoteEventReplyUpdateHandler        $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventLogger     = $this->createMock(NoteEventHandlerLogger::class);
        $this->replyRepository = $this->createMock(CommentReplyRepository::class);
        $this->bus             = $this->createMock(MessageBusInterface::class);
        $this->handler         = new NoteEventReplyUpdateHandler(
            $this->eventLogger,
            $this->replyRepository,
            $this->bus,
        );
    }

    public function testHandleSkipsUnchangedReplyMessage(): void
    {
        $event = $this->createEvent();
        $reply = new CommentReply();
        $reply->setMessage('Comment');
        $reply->setUpdateTimestamp(new DateTimeImmutable()->setTimestamp(123));
        $this->eventLogger->expects($this->once())
            ->method('logCommentUnchanged')
            ->with($event, '7:discussion:42', true);
        $this->replyRepository->expects($this->never())->method('save');
        $this->bus->expects($this->never())->method('dispatch');

        $this->handler->handle($event, $reply, '7:discussion:42');

        static::assertSame('Comment', $reply->getMessage());
        static::assertSame(123, $reply->getUpdateTimestamp()->getTimestamp());
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
        $this->replyRepository->expects($this->once())->method('save')->with($reply, true);
        $this->bus->expects($this->once())
            ->method('dispatch')
            ->with(new CommentReplyUpdated(456, 321, 789, 'Original comment', CommentModificationEnum::Gitlab))
            ->willReturn($this->envelope);
        $this->eventLogger->expects($this->once())
            ->method('logCommentMessageUpdated')
            ->with($event, '7:discussion:42', true);

        $this->handler->handle($event, $reply, '7:discussion:42');

        static::assertSame('Comment', $reply->getMessage());
        static::assertSame(CommentModificationEnum::Gitlab, $reply->getModifiedBy());
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

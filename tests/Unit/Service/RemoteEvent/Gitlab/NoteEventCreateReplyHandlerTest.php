<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\RemoteEvent\Gitlab;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Model\Api\Gitlab\MergeRequest;
use DR\Review\Model\Api\Gitlab\User as GitlabUser;
use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\CommentReplyFactory;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\NoteEventHandlerLogger;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEventCreateReplyHandler;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(NoteEventCreateReplyHandler::class)]
class NoteEventCreateReplyHandlerTest extends AbstractTestCase
{
    private NoteEventHandlerLogger&MockObject $eventLogger;
    private CommentReplyFactory&MockObject    $commentReplyFactory;
    private CommentReplyRepository&MockObject $commentReplyRepository;
    private MessageBusInterface&MockObject    $bus;
    private NoteEventCreateReplyHandler       $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventLogger            = $this->createMock(NoteEventHandlerLogger::class);
        $this->commentReplyFactory    = $this->createMock(CommentReplyFactory::class);
        $this->commentReplyRepository = $this->createMock(CommentReplyRepository::class);
        $this->bus                    = $this->createMock(MessageBusInterface::class);
        $this->handler                = new NoteEventCreateReplyHandler(
            $this->eventLogger,
            $this->commentReplyFactory,
            $this->commentReplyRepository,
            $this->bus,
        );
    }

    public function testHandleSkipsExistingReply(): void
    {
        $event   = $this->createEvent();
        $user    = new User();
        $comment = new Comment();
        $this->commentReplyRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['extReferenceId' => '7:discussion:42'])
            ->willReturn(new CommentReply());
        $this->eventLogger->expects($this->once())->method('logCommentAlreadyExists')->with($event, true);
        $this->commentReplyFactory->expects($this->never())->method('create');
        $this->bus->expects($this->never())->method('dispatch');

        $this->handler->handle($event, $user, $comment);
    }

    public function testHandleCreatesReply(): void
    {
        $event      = $this->createEvent();
        $repository = new Repository()->setDisplayName('Repository');
        $review     = new CodeReview()->setId(456)->setProjectId(123)->setRepository($repository);
        $comment    = new Comment()->setFilePath('new.php')->setReview($review);
        $user       = new User()->setId(789)->setName('User');
        $reply      = new CommentReply()->setId(321);
        $reply->setComment($comment);
        $reply->setUser($user);
        $reply->setMessage('Reply');

        $this->commentReplyRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['extReferenceId' => '7:discussion:42'])
            ->willReturn(null);
        $this->commentReplyFactory->expects($this->once())->method('create')->with($event, $user, $comment)->willReturn($reply);
        $this->commentReplyRepository->expects($this->once())->method('save')->with($reply, true);
        $this->bus->expects($this->once())
            ->method('dispatch')
            ->with(new CommentReplyAdded(456, 321, 789, 'Reply', 'new.php'))
            ->willReturn(new Envelope(new stdClass()));
        $this->eventLogger->expects($this->once())->method('logCommentReplyAddedSuccess')->with($event, $review, $user);

        $this->handler->handle($event, $user, $comment);
    }

    private function createEvent(): NoteEvent
    {
        $event                                = new NoteEvent();
        $event->id                            = 42;
        $event->mergeRequest                  = new MergeRequest();
        $event->mergeRequest->mergeRequestIId = 7;
        $event->discussionId                  = 'discussion';
        $event->noteType                      = 'MergeRequest';
        $event->action                        = 'create';
        $event->user                          = new GitlabUser();
        $event->user->id                      = 123;
        $event->user->name                    = 'name';

        return $event;
    }
}

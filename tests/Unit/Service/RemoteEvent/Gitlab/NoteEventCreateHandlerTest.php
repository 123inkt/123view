<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\RemoteEvent\Gitlab;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\User\User;
use DR\Review\Model\Api\Gitlab\MergeRequest;
use DR\Review\Model\Api\Gitlab\User as GitlabUser;
use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\Api\Gitlab\Discussions;
use DR\Review\Service\Api\Gitlab\GitlabCommentResolver;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\NoteEventHandlerLogger;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEventCreateCommentHandler;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEventCreateHandler;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEventCreateReplyHandler;
use DR\Review\Service\User\GitlabUserService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

#[CoversClass(NoteEventCreateHandler::class)]
class NoteEventCreateHandlerTest extends AbstractTestCase
{
    private NoteEventHandlerLogger&MockObject        $eventLogger;
    private GitlabUserService&MockObject             $userService;
    private Discussions&MockObject                   $discussions;
    private CommentRepository&MockObject             $commentRepository;
    private NoteEventCreateCommentHandler&MockObject $commentHandler;
    private NoteEventCreateReplyHandler&MockObject   $replyHandler;
    private NoteEventCreateHandler                   $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventLogger       = $this->createMock(NoteEventHandlerLogger::class);
        $this->userService       = $this->createMock(GitlabUserService::class);
        $this->discussions       = $this->createMock(Discussions::class);
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->commentHandler    = $this->createMock(NoteEventCreateCommentHandler::class);
        $this->replyHandler      = $this->createMock(NoteEventCreateReplyHandler::class);
        $this->handler           = new NoteEventCreateHandler(
            $this->eventLogger,
            $this->userService,
            new GitlabCommentResolver($this->discussions, $this->commentRepository),
            $this->commentHandler,
            $this->replyHandler,
        );
    }

    /**
     * @param 'create'|'update'      $action
     * @param 'MergeRequest'|'Issue' $noteType
     */
    #[TestWith([new NoteEvent(), 'create', 'MergeRequest', true])]
    #[TestWith([new NoteEvent(), 'update', 'MergeRequest', false])]
    #[TestWith([new NoteEvent(), 'create', 'Issue', false])]
    #[TestWith([new stdClass(), 'create', 'Issue', false])]
    public function testSupportsCreateMergeRequestNotes(object $event, string $action, string $noteType, bool $expected): void
    {
        $this->eventLogger->expects($this->never())->method(static::anything());
        $this->userService->expects($this->never())->method(static::anything());
        $this->discussions->expects($this->never())->method(static::anything());
        $this->commentRepository->expects($this->never())->method(static::anything());
        $this->commentHandler->expects($this->never())->method(static::anything());
        $this->replyHandler->expects($this->never())->method(static::anything());

        if ($event instanceof NoteEvent) {
            $event->action   = $action;
            $event->noteType = $noteType;
        }

        static::assertSame($expected, $this->handler->supports($event));
    }

    public function testHandleRoutesRootNoteToCommentHandler(): void
    {
        $event = $this->createEvent();
        $user  = $this->configureResolvedUser();
        $this->discussions->expects($this->once())
            ->method('getDiscussion')
            ->with(321, 7, 'discussion')
            ->willReturn(['id' => 'discussion', 'notes' => [['id' => 42]]]);
        $this->eventLogger->expects($this->never())->method(static::anything());
        $this->commentRepository->expects($this->never())->method('findOneBy');
        $this->commentHandler->expects($this->once())->method('handle')->with($event, $user);
        $this->replyHandler->expects($this->never())->method('handle');

        $this->handler->handle($event);
    }

    public function testHandleRoutesReplyToReplyHandler(): void
    {
        $event   = $this->createEvent();
        $comment = new Comment();
        $user    = $this->configureResolvedUser();
        $this->discussions->expects($this->once())
            ->method('getDiscussion')
            ->with(321, 7, 'discussion')
            ->willReturn(['id' => 'discussion', 'notes' => [['id' => 41], ['id' => 42]]]);
        $this->commentRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['extReferenceId' => '7:discussion:41'])
            ->willReturn($comment);
        $this->eventLogger->expects($this->never())->method(static::anything());
        $this->commentHandler->expects($this->never())->method('handle');
        $this->replyHandler->expects($this->once())->method('handle')->with($event, $user, $comment);

        $this->handler->handle($event);
    }

    public function testHandleSkipsWhenGitlabUserIsNotFound(): void
    {
        $event = $this->createEvent();
        $this->discussions->expects($this->once())
            ->method('getDiscussion')
            ->with(321, 7, 'discussion')
            ->willReturn(['id' => 'discussion', 'notes' => [['id' => 42]]]);
        $this->commentRepository->expects($this->never())->method('findOneBy');
        $this->userService->expects($this->once())
            ->method('getUser')
            ->with(123, 'name')
            ->willReturn(null);
        $this->eventLogger->expects($this->once())->method('logUserNotFound')->with($event, $event->user);
        $this->commentHandler->expects($this->never())->method('handle');
        $this->replyHandler->expects($this->never())->method('handle');

        $this->handler->handle($event);
    }

    private function configureResolvedUser(): User
    {
        $user = new User()->setEmail('user@example.com');
        $this->userService->expects($this->once())
            ->method('getUser')
            ->with(123, 'name')
            ->willReturn($user);

        return $user;
    }

    private function createEvent(): NoteEvent
    {
        $event                                = new NoteEvent();
        $event->id                            = 42;
        $event->projectId                     = 321;
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

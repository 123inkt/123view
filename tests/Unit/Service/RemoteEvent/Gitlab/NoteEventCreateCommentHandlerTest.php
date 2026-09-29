<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\RemoteEvent\Gitlab;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Model\Api\Gitlab\MergeRequest;
use DR\Review\Model\Api\Gitlab\Position;
use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\CommentFactory;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\NoteEventHandlerLogger;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\RevisionFilepathMatcher;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEventCreateCommentHandler;
use DR\Review\Service\Revision\BranchRevisionService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(NoteEventCreateCommentHandler::class)]
class NoteEventCreateCommentHandlerTest extends AbstractTestCase
{
    private NoteEventHandlerLogger&MockObject  $eventLogger;
    private RepositoryRepository&MockObject    $repositoryRepository;
    private BranchRevisionService&MockObject   $branchRevisionService;
    private RevisionFilepathMatcher&MockObject $revisionMatcher;
    private CommentFactory&MockObject          $commentFactory;
    private CommentRepository&MockObject       $commentRepository;
    private NoteEventCreateCommentHandler      $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventLogger           = $this->createMock(NoteEventHandlerLogger::class);
        $this->repositoryRepository  = $this->createMock(RepositoryRepository::class);
        $this->branchRevisionService = $this->createMock(BranchRevisionService::class);
        $this->revisionMatcher       = $this->createMock(RevisionFilepathMatcher::class);
        $this->commentFactory        = $this->createMock(CommentFactory::class);
        $this->commentRepository     = $this->createMock(CommentRepository::class);
        $this->handler               = new NoteEventCreateCommentHandler(
            $this->eventLogger,
            $this->repositoryRepository,
            $this->branchRevisionService,
            $this->revisionMatcher,
            $this->commentFactory,
            $this->commentRepository,
        );
    }

    public function testHandleSkipsExistingComment(): void
    {
        $event = $this->createEvent();
        $user  = new User();
        $this->commentRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['extReferenceId' => '7:discussion:42'])
            ->willReturn(new Comment());
        $this->eventLogger->expects($this->once())->method('logCommentAlreadyExists')->with($event);
        $this->repositoryRepository->expects($this->never())->method('findByProperty');
        $this->branchRevisionService->expects($this->never())->method(static::anything());
        $this->revisionMatcher->expects($this->never())->method(static::anything());
        $this->commentFactory->expects($this->never())->method(static::anything());

        $this->handler->handle($event, $user);
    }

    public function testHandleSkipsUnknownRepository(): void
    {
        [$event, $user] = $this->configureResolvedUser();
        $this->repositoryRepository->expects($this->once())
            ->method('findByProperty')
            ->with('gitlab-project-id', '321')
            ->willReturn(null);
        $this->eventLogger->expects($this->once())->method('logRepositoryNotFound')->with($event);
        $this->branchRevisionService->expects($this->never())->method('getRevisionsFor');
        $this->revisionMatcher->expects($this->never())->method(static::anything());
        $this->commentFactory->expects($this->never())->method(static::anything());

        $this->handler->handle($event, $user);
    }

    public function testHandleSkipsInactiveRepository(): void
    {
        [$event, $user] = $this->configureResolvedUser();
        $repository = new Repository()->setActive(false);
        $this->repositoryRepository->expects($this->once())->method('findByProperty')->willReturn($repository);
        $this->eventLogger->expects($this->once())->method('logRepositoryNotFound')->with($event);
        $this->branchRevisionService->expects($this->never())->method('getRevisionsFor');
        $this->revisionMatcher->expects($this->never())->method(static::anything());
        $this->commentFactory->expects($this->never())->method(static::anything());

        $this->handler->handle($event, $user);
    }

    public function testHandleSkipsWhenBranchHasNoRevisions(): void
    {
        [$event, $user] = $this->configureResolvedUser();
        $repository = new Repository()->setActive(true);
        $this->repositoryRepository->expects($this->once())->method('findByProperty')->willReturn($repository);
        $this->branchRevisionService->expects($this->once())
            ->method('getRevisionsFor')
            ->with($repository, 'origin/feature', 'main')
            ->willReturn([]);
        $this->eventLogger->expects($this->once())->method('logRevisionsNotFound')->with($event);
        $this->revisionMatcher->expects($this->never())->method('matchRevision');
        $this->commentFactory->expects($this->never())->method(static::anything());

        $this->handler->handle($event, $user);
    }

    public function testHandleIgnoresRevisionsWithoutAReview(): void
    {
        [$event, $user] = $this->configureResolvedUser();
        $repository = new Repository()->setActive(true);
        $revision   = new Revision()->setCommitHash('commit-sha');
        $this->repositoryRepository->expects($this->once())->method('findByProperty')->willReturn($repository);
        $this->branchRevisionService->expects($this->once())->method('getRevisionsFor')->willReturn([$revision]);
        $this->revisionMatcher->expects($this->once())->method('matchRevision')->with($event, [])->willReturn([null, null]);
        $this->eventLogger->expects($this->once())->method('logRevisionForFilenameNotFound')->with($event);
        $this->commentFactory->expects($this->never())->method(static::anything());

        $this->handler->handle($event, $user);
    }

    public function testHandleSkipsWhenFileCannotBeMatched(): void
    {
        [$event, $user] = $this->configureResolvedUser();
        $repository = new Repository()->setActive(true);
        $review     = new CodeReview();
        $revision   = new Revision()->setCommitHash('commit-sha');
        $revision->setReview($review);
        $this->repositoryRepository->expects($this->once())->method('findByProperty')->willReturn($repository);
        $this->branchRevisionService->expects($this->once())->method('getRevisionsFor')->willReturn([$revision]);
        $this->revisionMatcher->expects($this->once())->method('matchRevision')->with($event, [$revision])->willReturn([null, null]);
        $this->eventLogger->expects($this->once())->method('logRevisionForFilenameNotFound')->with($event);
        $this->commentFactory->expects($this->never())->method(static::anything());

        $this->handler->handle($event, $user);
    }

    public function testHandleCreatesComment(): void
    {
        [$event, $user] = $this->configureResolvedUser();
        $repository = new Repository()->setActive(true);
        $review     = new CodeReview();
        $revision   = new Revision()->setCommitHash('commit-sha');
        $revision->setReview($review);
        $comment = new Comment();
        $this->repositoryRepository->expects($this->once())->method('findByProperty')->willReturn($repository);
        $this->branchRevisionService->expects($this->once())->method('getRevisionsFor')->willReturn([$revision]);
        $this->revisionMatcher->expects($this->once())->method('matchRevision')->with($event, [$revision])->willReturn([$revision, 'new.php']);
        $this->commentFactory->expects($this->once())->method('create')->with($event, $user, $revision, 'new.php')->willReturn($comment);
        $this->commentRepository->expects($this->once())->method('save')->with($comment, true);
        $this->eventLogger->expects($this->once())->method('logCommentAddedSuccess')->with($event, $review, $user);

        $this->handler->handle($event, $user);
    }

    /**
     * @return array{0: NoteEvent, 1: User}
     */
    private function configureResolvedUser(): array
    {
        $event = $this->createEvent();
        $user  = new User()->setEmail('user@example.com');
        $this->commentRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['extReferenceId' => '7:discussion:42'])
            ->willReturn(null);

        return [$event, $user];
    }

    private function createEvent(): NoteEvent
    {
        $event                                = new NoteEvent();
        $event->id                            = 42;
        $event->projectId                     = 321;
        $event->mergeRequest                  = new MergeRequest();
        $event->mergeRequest->mergeRequestIId = 7;
        $event->mergeRequest->sourceBranch    = 'feature';
        $event->mergeRequest->targetBranch    = 'main';
        $event->discussionId                  = 'discussion';
        $event->note                          = 'Comment';
        $event->noteType                      = 'MergeRequest';
        $event->action                        = 'create';
        $event->position                      = new Position();
        $event->position->newPath             = 'new.php';
        $event->user                          = new \DR\Review\Model\Api\Gitlab\User();
        $event->user->id                      = 123;
        $event->user->name                    = 'name';

        return $event;
    }
}

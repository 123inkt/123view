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
use DR\Review\Model\Api\Gitlab\User as GitlabUser;
use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\CommentFactory;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\NoteEventHandlerLogger;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\RevisionFilepathMatcher;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEventCreateHandler;
use DR\Review\Service\Revision\BranchRevisionService;
use DR\Review\Service\User\GitlabUserService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

#[CoversClass(NoteEventCreateHandler::class)]
class NoteEventCreateHandlerTest extends AbstractTestCase
{
    private NoteEventHandlerLogger&MockObject  $eventLogger;
    private RepositoryRepository&MockObject    $repositoryRepository;
    private GitlabUserService&MockObject       $userService;
    private BranchRevisionService&MockObject   $branchRevisionService;
    private RevisionFilepathMatcher&MockObject $revisionMatcher;
    private CommentFactory&MockObject          $commentFactory;
    private CommentRepository&MockObject       $commentRepository;
    private NoteEventCreateHandler             $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventLogger           = $this->createMock(NoteEventHandlerLogger::class);
        $this->repositoryRepository  = $this->createMock(RepositoryRepository::class);
        $this->userService           = $this->createMock(GitlabUserService::class);
        $this->branchRevisionService = $this->createMock(BranchRevisionService::class);
        $this->revisionMatcher       = $this->createMock(RevisionFilepathMatcher::class);
        $this->commentFactory        = $this->createMock(CommentFactory::class);
        $this->commentRepository     = $this->createMock(CommentRepository::class);
        $this->handler               = new NoteEventCreateHandler(
            $this->eventLogger,
            $this->repositoryRepository,
            $this->userService,
            $this->branchRevisionService,
            $this->revisionMatcher,
            $this->commentFactory,
            $this->commentRepository
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
        $this->repositoryRepository->expects($this->never())->method(static::anything());
        $this->userService->expects($this->never())->method(static::anything());
        $this->branchRevisionService->expects($this->never())->method(static::anything());
        $this->revisionMatcher->expects($this->never())->method(static::anything());
        $this->commentFactory->expects($this->never())->method(static::anything());
        $this->commentRepository->expects($this->never())->method(static::anything());

        if ($event instanceof NoteEvent) {
            $event->action   = $action;
            $event->noteType = $noteType;
        }

        static::assertSame($expected, $this->handler->supports($event));
    }

    public function testHandleSkipsExistingComment(): void
    {
        $event = $this->createEvent();
        $this->commentRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['extReferenceId' => '7:discussion:42'])
            ->willReturn(new Comment());
        $this->eventLogger->expects($this->once())->method('logCommentAlreadyExists')->with($event);
        $this->repositoryRepository->expects($this->never())->method(static::anything());
        $this->userService->expects($this->never())->method(static::anything());
        $this->branchRevisionService->expects($this->never())->method(static::anything());
        $this->revisionMatcher->expects($this->never())->method(static::anything());
        $this->commentFactory->expects($this->never())->method(static::anything());

        $this->handler->handle($event);
    }

    public function testSkipsWhenGitlabUserNotFound(): void
    {
        $event = $this->createEvent();
        $this->commentRepository->expects($this->once())->method('findOneBy')->with(['extReferenceId' => '7:discussion:42'])->willReturn(null);
        $this->userService->expects($this->once())->method('getUser')->with(123, 'name')->willReturn(null);
        $this->eventLogger->expects($this->once())->method('logUserNotFound')->with($event, $event->user);
        $this->repositoryRepository->expects($this->never())->method(static::anything());
        $this->branchRevisionService->expects($this->never())->method(static::anything());
        $this->revisionMatcher->expects($this->never())->method(static::anything());
        $this->commentFactory->expects($this->never())->method(static::anything());

        $this->handler->handle($event);
    }

    public function testHandleSkipsUnknownRepository(): void
    {
        [$event] = $this->configureResolvedUser();
        $this->repositoryRepository->expects($this->once())
            ->method('findByProperty')
            ->with('gitlab-project-id', '321')
            ->willReturn(null);
        $this->eventLogger->expects($this->once())->method('logRepositoryNotFound')->with($event);
        $this->branchRevisionService->expects($this->never())->method(static::anything());
        $this->revisionMatcher->expects($this->never())->method(static::anything());
        $this->commentFactory->expects($this->never())->method(static::anything());

        $this->handler->handle($event);
    }

    public function testHandleSkipsInactiveRepository(): void
    {
        [$event] = $this->configureResolvedUser();
        $repository = new Repository()->setActive(false);
        $this->repositoryRepository->expects($this->once())->method('findByProperty')->with('gitlab-project-id', '321')->willReturn($repository);
        $this->eventLogger->expects($this->once())->method('logRepositoryNotFound')->with($event);
        $this->branchRevisionService->expects($this->never())->method(static::anything());
        $this->revisionMatcher->expects($this->never())->method(static::anything());
        $this->commentFactory->expects($this->never())->method(static::anything());

        $this->handler->handle($event);
    }

    public function testHandleSkipsWhenBranchHasNoRevisions(): void
    {
        [$event] = $this->configureResolvedUser();
        $repository = new Repository()->setActive(true);
        $this->repositoryRepository->expects($this->once())->method('findByProperty')->with('gitlab-project-id', '321')->willReturn($repository);
        $this->branchRevisionService->expects($this->once())
            ->method('getRevisionsFor')
            ->with($repository, 'origin/feature', 'main')
            ->willReturn([]);
        $this->eventLogger->expects($this->once())->method('logRevisionsNotFound')->with($event);
        $this->revisionMatcher->expects($this->never())->method(static::anything());
        $this->commentFactory->expects($this->never())->method(static::anything());

        $this->handler->handle($event);
    }

    public function testHandleIgnoresRevisionsWithoutAReview(): void
    {
        [$event] = $this->configureResolvedUser();
        $repository = new Repository()->setActive(true);
        $revision   = new Revision()->setCommitHash('commit-sha');
        $this->repositoryRepository->expects($this->once())->method('findByProperty')->with('gitlab-project-id', '321')->willReturn($repository);
        $this->branchRevisionService->expects($this->once())
            ->method('getRevisionsFor')
            ->with($repository, 'origin/feature', 'main')
            ->willReturn([$revision]);
        $this->revisionMatcher->expects($this->once())->method('matchRevision')->with($event, [])->willReturn([null, null]);
        $this->eventLogger->expects($this->once())->method('logRevisionForFilenameNotFound')->with($event);
        $this->commentFactory->expects($this->never())->method(static::anything());

        $this->handler->handle($event);
    }

    public function testHandleSkipsWhenFileCannotBeMatched(): void
    {
        [$event] = $this->configureResolvedUser();
        $repository = new Repository()->setActive(true);
        $review     = new CodeReview();
        $revision   = new Revision()->setCommitHash('commit-sha');
        $revision->setReview($review);
        $this->repositoryRepository->expects($this->once())->method('findByProperty')->with('gitlab-project-id', '321')->willReturn($repository);
        $this->branchRevisionService->expects($this->once())
            ->method('getRevisionsFor')
            ->with($repository, 'origin/feature', 'main')
            ->willReturn([$revision]);
        $this->revisionMatcher->expects($this->once())->method('matchRevision')->with($event, [$revision])->willReturn([null, null]);
        $this->eventLogger->expects($this->once())->method('logRevisionForFilenameNotFound')->with($event);
        $this->commentFactory->expects($this->never())->method(static::anything());

        $this->handler->handle($event);
    }

    public function testHandleCreatesComment(): void
    {
        [$event, $user] = $this->configureResolvedUser();
        $repository = new Repository()->setActive(true);
        $review     = new CodeReview();
        $revision   = new Revision()->setCommitHash('commit-sha');
        $revision->setReview($review);
        $comment = new Comment();

        $this->repositoryRepository->expects($this->once())->method('findByProperty')->with('gitlab-project-id', '321')->willReturn($repository);
        $this->branchRevisionService->expects($this->once())
            ->method('getRevisionsFor')
            ->with($repository, 'origin/feature', 'main')
            ->willReturn([$revision]);
        $this->revisionMatcher->expects($this->once())->method('matchRevision')->with($event, [$revision])->willReturn([$revision, 'new.php']);
        $this->commentFactory->expects($this->once())->method('create')->with($event, $user, $revision, 'new.php')->willReturn($comment);
        $this->commentRepository->expects($this->once())->method('save')->with($comment, true);
        $this->eventLogger->expects($this->once())->method('logCommentAddedSuccess')->with($event, $review, $user);

        $this->handler->handle($event);
    }

    /**
     * @return array{0: NoteEvent, 1: User}
     */
    private function configureResolvedUser(): array
    {
        $event             = $this->createEvent();
        $user              = new User()->setEmail('user@example.com');

        $this->commentRepository->expects($this->once())->method('findOneBy')->with(['extReferenceId' => '7:discussion:42'])->willReturn(null);
        $this->userService->expects($this->once())->method('getUser')->with(123, 'name')->willReturn($user);

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
        $event->position->headSha             = 'head-sha';
        $event->user                          = new GitlabUser();
        $event->user->id                      = 123;
        $event->user->name                    = 'name';
        $event->user->email                   = 'user@example.com';

        return $event;
    }
}

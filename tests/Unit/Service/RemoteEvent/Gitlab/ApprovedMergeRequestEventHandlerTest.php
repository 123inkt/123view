<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\RemoteEvent\Gitlab;

use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\User\User;
use DR\Review\Model\Api\Gitlab\Project;
use DR\Review\Model\Api\Gitlab\User as GitlabUser;
use DR\Review\Model\Webhook\Gitlab\MergeRequestEvent;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\CodeReview\ChangeReviewerStateService;
use DR\Review\Service\RemoteEvent\Gitlab\ApprovedMergeRequestEventHandler;
use DR\Review\Service\User\GitlabUserService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(ApprovedMergeRequestEventHandler::class)]
class ApprovedMergeRequestEventHandlerTest extends AbstractTestCase
{
    private RepositoryRepository&MockObject       $repositoryRepository;
    private CodeReviewRepository&MockObject       $reviewRepository;
    private GitlabUserService&MockObject          $userService;
    private ChangeReviewerStateService&MockObject $changeReviewerStateService;
    private ApprovedMergeRequestEventHandler      $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryRepository       = $this->createMock(RepositoryRepository::class);
        $this->reviewRepository           = $this->createMock(CodeReviewRepository::class);
        $this->userService                = $this->createMock(GitlabUserService::class);
        $this->changeReviewerStateService = $this->createMock(ChangeReviewerStateService::class);
        $this->handler                    = new ApprovedMergeRequestEventHandler(
            $this->repositoryRepository,
            $this->reviewRepository,
            $this->userService,
            $this->changeReviewerStateService
        );
    }

    /**
     * @throws Throwable
     */
    public function testHandleShouldOnlyAcceptApprovedMergeRequest(): void
    {
        $event         = new MergeRequestEvent();
        $event->action = 'open';

        $this->repositoryRepository->expects($this->never())->method('findByProperty');
        $this->handler->handle($event);
    }

    /**
     * @throws Throwable
     */
    public function testHandleShouldAbortForUnknownRepository(): void
    {
        $project        = new Project();
        $project->id    = 123;
        $event          = new MergeRequestEvent();
        $event->project = $project;
        $event->action  = 'approved';

        $this->repositoryRepository->expects($this->once())->method('findByProperty')->with('gitlab-project-id', 123)->willReturn(null);
        $this->userService->expects($this->never())->method('getUser');

        $this->handler->handle($event);
    }

    /**
     * @throws Throwable
     */
    public function testHandleShouldIgnoreInactiveRepositories(): void
    {
        $project        = new Project();
        $project->id    = 123;
        $event          = new MergeRequestEvent();
        $event->project = $project;
        $event->action  = 'approved';

        $repository = (new Repository())->setActive(false);

        $this->repositoryRepository->expects($this->once())->method('findByProperty')->with('gitlab-project-id', 123)->willReturn($repository);
        $this->userService->expects($this->never())->method('getUser');

        $this->handler->handle($event);
    }

    /**
     * @throws Throwable
     */
    public function testHandleSkipForUnknownUser(): void
    {
        $user           = new GitlabUser();
        $user->id       = 789;
        $user->name     = 'name';
        $project        = new Project();
        $project->id    = 123;
        $event          = new MergeRequestEvent();
        $event->project = $project;
        $event->user    = $user;
        $event->action  = 'approved';

        $repository = (new Repository())->setActive(true);

        $this->repositoryRepository->expects($this->once())->method('findByProperty')->with('gitlab-project-id', 123)->willReturn($repository);
        $this->userService->expects($this->once())->method('getUser')->with(789, 'name')->willReturn(null);

        $this->handler->handle($event);
    }

    /**
     * @throws Throwable
     */
    public function testHandleReviewsSkipClosedReview(): void
    {
        $user                = new GitlabUser();
        $user->id            = 789;
        $user->name          = 'name';
        $project             = new Project();
        $project->id         = 123;
        $event               = new MergeRequestEvent();
        $event->project      = $project;
        $event->user         = $user;
        $event->sourceBranch = 'branch';
        $event->action       = 'approved';

        $user       = new User();
        $review     = (new CodeReview())->setState(CodeReviewStateType::CLOSED);
        $repository = (new Repository())->setId(456)->setActive(true);

        $this->repositoryRepository->expects($this->once())->method('findByProperty')->with('gitlab-project-id', 123)->willReturn($repository);
        $this->userService->expects($this->once())->method('getUser')->with(789, 'name')->willReturn($user);
        $this->reviewRepository->expects($this->once())->method('findByBranchName')->with(456, 'branch')->willReturn([$review]);
        $this->changeReviewerStateService->expects($this->never())->method('changeState');

        $this->handler->handle($event);
    }

    /**
     * @throws Throwable
     */
    public function testHandleReviews(): void
    {
        $user                = new GitlabUser();
        $user->id            = 789;
        $user->name          = 'name';
        $project             = new Project();
        $project->id         = 123;
        $event               = new MergeRequestEvent();
        $event->project      = $project;
        $event->user         = $user;
        $event->sourceBranch = 'branch';
        $event->action       = 'approved';

        $user       = new User();
        $review     = new CodeReview();
        $repository = (new Repository())->setId(456)->setActive(true);

        $this->repositoryRepository->expects($this->once())->method('findByProperty')->with('gitlab-project-id', 123)->willReturn($repository);
        $this->userService->expects($this->once())->method('getUser')->with(789, 'name')->willReturn($user);
        $this->reviewRepository->expects($this->once())->method('findByBranchName')->with(456, 'branch')->willReturn([$review]);
        $this->changeReviewerStateService->expects($this->once())->method('changeState')->with($review, $user, CodeReviewerStateType::ACCEPTED);

        $this->handler->handle($event);
    }
}

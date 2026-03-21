<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Api\Gitlab;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Repository\RepositoryProperty;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\User\User;
use DR\Review\Service\Api\Gitlab\GitlabApi;
use DR\Review\Service\Api\Gitlab\GitlabApiProvider;
use DR\Review\Service\Api\Gitlab\MergeRequests;
use DR\Review\Service\Api\Gitlab\ReviewApprovalService;
use DR\Review\Service\Api\Gitlab\ReviewMergeRequestService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(ReviewApprovalService::class)]
class ReviewApprovalServiceTest extends AbstractTestCase
{
    private GitlabApiProvider&MockObject         $apiProvider;
    private ReviewMergeRequestService&MockObject $mergeRequestService;
    private GitlabApi&MockObject                 $gitlabApi;
    private MergeRequests&MockObject             $mergeRequests;
    private ReviewApprovalService                $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiProvider         = $this->createMock(GitlabApiProvider::class);
        $this->mergeRequestService = $this->createMock(ReviewMergeRequestService::class);
        $this->gitlabApi           = $this->createMock(GitlabApi::class);
        $this->mergeRequests       = $this->createMock(MergeRequests::class);
        $this->service             = new ReviewApprovalService($this->apiProvider, $this->mergeRequestService);
    }

    /**
     * @throws Throwable
     */
    public function testApproveShouldSkipWithoutApi(): void
    {
        $user               = new User();
        $reviewer           = (new CodeReviewer())->setUser($user);
        $repositoryProperty = new RepositoryProperty('gitlab-project-id', '123');
        $repository         = new Repository();
        $repository->getRepositoryProperties()->set('gitlab-project-id', $repositoryProperty);
        $review = (new CodeReview())->setId(456)->setRepository($repository);

        $this->apiProvider->expects($this->once())->method('create')->with($repository, $user)->willReturn(null);
        $this->mergeRequestService->expects($this->never())->method('retrieveMergeRequestIID');
        $this->gitlabApi->expects($this->never())->method('mergeRequests');
        $this->mergeRequests->expects($this->never())->method('approve');

        $this->service->approve($review, $reviewer, true);
    }

    /**
     * @throws Throwable
     */
    public function testApproveShouldSkipWithoutMergeRequestIId(): void
    {
        $user               = new User();
        $reviewer           = (new CodeReviewer())->setUser($user);
        $repositoryProperty = new RepositoryProperty('gitlab-project-id', '123');
        $repository         = new Repository();
        $repository->getRepositoryProperties()->set('gitlab-project-id', $repositoryProperty);
        $review = (new CodeReview())->setId(456)->setRepository($repository);

        $this->apiProvider->expects($this->once())->method('create')->with($repository, $user)->willReturn($this->gitlabApi);
        $this->mergeRequestService->expects($this->once())->method('retrieveMergeRequestIID')->with($this->gitlabApi, $review)->willReturn(null);
        $this->gitlabApi->expects($this->never())->method('mergeRequests');
        $this->mergeRequests->expects($this->never())->method('approve');

        $this->service->approve($review, $reviewer, true);
    }

    /**
     * @throws Throwable
     */
    public function testApproveShouldApprove(): void
    {
        $user               = new User();
        $reviewer           = (new CodeReviewer())->setUser($user);
        $repositoryProperty = new RepositoryProperty('gitlab-project-id', '123');
        $repository         = new Repository();
        $repository->getRepositoryProperties()->set('gitlab-project-id', $repositoryProperty);
        $review = (new CodeReview())->setId(456)->setRepository($repository);

        $this->apiProvider->expects($this->once())->method('create')->with($repository, $user)->willReturn($this->gitlabApi);
        $this->mergeRequestService->expects($this->once())->method('retrieveMergeRequestIID')->with($this->gitlabApi, $review)->willReturn(543);
        $this->gitlabApi->expects($this->once())->method('mergeRequests')->willReturn($this->mergeRequests);
        $this->mergeRequests->expects($this->once())->method('approve')->with(123, 543);

        $this->service->approve($review, $reviewer, true);
    }

    /**
     * @throws Throwable
     */
    public function testApproveShouldUnapprove(): void
    {
        $user               = new User();
        $reviewer           = (new CodeReviewer())->setUser($user);
        $repositoryProperty = new RepositoryProperty('gitlab-project-id', '123');
        $repository         = new Repository();
        $repository->getRepositoryProperties()->set('gitlab-project-id', $repositoryProperty);
        $review = (new CodeReview())->setId(456)->setRepository($repository);

        $this->apiProvider->expects($this->once())->method('create')->with($repository, $user)->willReturn($this->gitlabApi);
        $this->mergeRequestService->expects($this->once())->method('retrieveMergeRequestIID')->with($this->gitlabApi, $review)->willReturn(543);
        $this->gitlabApi->expects($this->once())->method('mergeRequests')->willReturn($this->mergeRequests);
        $this->mergeRequests->expects($this->once())->method('unapprove')->with(123, 543);

        $this->service->approve($review, $reviewer, false);
    }
}

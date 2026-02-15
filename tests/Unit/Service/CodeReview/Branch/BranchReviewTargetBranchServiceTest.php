<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview\Branch;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Repository\RepositoryProperty;
use DR\Review\ExternalTool\Gitlab\GitlabService;
use DR\Review\Service\CodeReview\Branch\BranchReviewTargetBranchService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(BranchReviewTargetBranchService::class)]
class BranchReviewTargetBranchServiceTest extends AbstractTestCase
{
    private GitlabService&MockObject        $gitlabService;
    private BranchReviewTargetBranchService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gitlabService = $this->createMock(GitlabService::class);
        $this->service       = new BranchReviewTargetBranchService($this->gitlabService);
    }

    /**
     * @throws Throwable
     */
    public function testGetTargetBranchWithGitlab(): void
    {
        $repository = (new Repository())->setGitType('gitlab')->setMainBranchName('main');
        $repository->setRepositoryProperty(new RepositoryProperty('gitlab-project-id', '123'));

        $this->gitlabService->expects($this->once())->method('getMergeRequestTargetBranch')->with(123, 'branch')->willReturn('target-branch');

        static::assertSame('target-branch', $this->service->getTargetBranch($repository, 'origin/branch'));
    }

    /**
     * @throws Throwable
     */
    public function testGetDefaultTargetBranch(): void
    {
        $this->gitlabService->expects($this->never())->method('getMergeRequestTargetBranch');
        $repository = (new Repository())->setMainBranchName('main');

        static::assertSame('main', $this->service->getTargetBranch($repository, 'branch'));
    }
}

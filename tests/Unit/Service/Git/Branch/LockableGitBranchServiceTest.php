<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Branch;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\Branch\GitBranchService;
use DR\Review\Service\Git\Branch\LockableGitBranchService;
use DR\Review\Service\Git\GitRepositoryLockManager;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(LockableGitBranchService::class)]
class LockableGitBranchServiceTest extends AbstractTestCase
{
    private GitRepositoryLockManager&MockObject $lockManager;
    private GitBranchService&MockObject         $branchService;
    private LockableGitBranchService            $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lockManager   = $this->createMock(GitRepositoryLockManager::class);
        $this->branchService = $this->createMock(GitBranchService::class);
        $this->service       = new LockableGitBranchService($this->lockManager, $this->branchService);
    }

    /**
     * @throws RepositoryException
     */
    public function testGetRemoteBranches(): void
    {
        $repository = new Repository();

        $this->lockManager->expects($this->once())->method('start')
            ->with($repository)
            ->willReturnCallback(static fn($repository, $callback) => $callback());
        $this->branchService->expects($this->once())->method('getRemoteBranches')->with($repository, true)->willReturn(['foo']);

        static::assertSame(['foo'], $this->service->getRemoteBranches($repository, true));
    }

    public function testTryDeleteBranch(): void
    {
        $repository = new Repository();

        $this->lockManager->expects($this->once())->method('start')
            ->with($repository)
            ->willReturnCallback(static fn($repository, $callback) => $callback());
        $this->branchService->expects($this->once())->method('tryDeleteBranch')->with($repository, 'ref')->willReturn(false);

        static::assertFalse($this->service->tryDeleteBranch($repository, 'ref'));
    }

    /**
     * @throws RepositoryException
     */
    public function testDeleteBranch(): void
    {
        $repository = new Repository();

        $this->lockManager->expects($this->once())->method('start')
            ->with($repository)
            ->willReturnCallback(static fn($repository, $callback) => $callback());
        $this->branchService->expects($this->once())->method('deleteBranch')->with($repository, true);

        $this->service->deleteBranch($repository, 'ref');
    }

}

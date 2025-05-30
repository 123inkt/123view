<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Remote;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\GitRepositoryLockManager;
use DR\Review\Service\Git\Remote\GitRemoteService;
use DR\Review\Service\Git\Remote\LockableGitRemoteService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(LockableGitRemoteService::class)]
class LockableGitRemoteServiceTest extends AbstractTestCase
{
    private GitRepositoryLockManager&MockObject $lockManager;
    private GitRemoteService&MockObject $remoteService;
    private LockableGitRemoteService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lockManager   = $this->createMock(GitRepositoryLockManager::class);
        $this->remoteService = $this->createMock(GitRemoteService::class);
        $this->service       = new LockableGitRemoteService($this->lockManager, $this->remoteService);
    }

    /**
     * @throws RepositoryException
     */
    public function testUpdateRemoteUrl(): void
    {
        $repository = new Repository();
        $repository->setId(123);

        $this->lockManager->expects($this->once())
            ->method('start')
            ->with($repository)
            ->willReturnCallback(static fn($repository, $callback) => $callback());
        $this->remoteService->expects($this->once())->method('updateRemoteUrl')->with($repository);

        $this->service->updateRemoteUrl($repository);
    }
}

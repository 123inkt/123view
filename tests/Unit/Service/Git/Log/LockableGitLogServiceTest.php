<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Log;

use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\Git\GitRepositoryLockManager;
use DR\Review\Service\Git\Log\GitLogService;
use DR\Review\Service\Git\Log\LockableGitLogService;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(LockableGitLogService::class)]
class LockableGitLogServiceTest extends AbstractTestCase
{
    private GitRepositoryLockManager&MockObject $lockManager;
    private GitLogService&MockObject            $logService;
    private LockableGitLogService               $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->lockManager = $this->createMock(GitRepositoryLockManager::class);
        $this->logService  = $this->createMock(GitLogService::class);
        $this->service     = new LockableGitLogService($this->lockManager, $this->logService);
    }

    /**
     * @throws Exception
     */
    public function testGetCommitHashes(): void
    {
        $repository = new Repository();
        $repository->setId(123);

        $this->lockManager->expects($this->once())
            ->method('start')
            ->with($repository)
            ->willReturnCallback(static fn($repository, $callback) => $callback());
        $this->logService->expects($this->once())->method('getCommitHashes')->with($repository)->willReturn(['hash']);

        $result = $this->service->getCommitHashes($repository);
        static::assertSame(['hash'], $result);
    }

    /**
     * @throws Exception
     */
    public function testGetCommitsFromRange(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $commit = static::createStub(Commit::class);

        $this->lockManager->expects($this->once())
            ->method('start')
            ->with($repository)
            ->willReturnCallback(static fn($repository, $callback) => $callback());
        $this->logService->expects($this->once())->method('getCommitsFromRange')->with($repository, 'fromHash', 'toHash')->willReturn([$commit]);

        $result = $this->service->getCommitsFromRange($repository, 'fromHash', 'toHash');
        static::assertSame([$commit], $result);
    }
}

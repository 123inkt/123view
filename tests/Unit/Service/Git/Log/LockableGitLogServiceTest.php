<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\Log;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Git\Commit;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Service\Git\GitRepositoryLockManager;
use DR\GitCommitNotification\Service\Git\Log\GitLogService;
use DR\GitCommitNotification\Service\Git\Log\LockableGitLogService;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\Log\LockableGitLogService
 * @covers ::__construct
 */
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
     * @covers ::getCommitsSince
     * @throws Exception
     */
    public function testGetCommitsSince(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $revision = new Revision();
        $revision->setCommitHash('hash');
        $limit  = 10;
        $commit = $this->createMock(Commit::class);

        $this->lockManager->expects(self::once())
            ->method('start')
            ->with($repository)
            ->willReturnCallback(static fn($repository, $callback) => $callback());
        $this->logService->expects(self::once())->method('getCommitsSince')->with($repository, $revision, $limit)->willReturn([$commit]);

        $result = $this->service->getCommitsSince($repository, $revision, $limit);
        static::assertSame([$commit], $result);
    }
}

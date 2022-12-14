<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Log;

use DateTime;
use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\Git\GitRepositoryLockManager;
use DR\Review\Service\Git\Log\GitLogService;
use DR\Review\Service\Git\Log\LockableGitLogService;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Service\Git\Log\LockableGitLogService
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
        $since  = new DateTime();
        $limit  = 10;
        $commit = $this->createMock(Commit::class);

        $this->lockManager->expects(self::once())
            ->method('start')
            ->with($repository)
            ->willReturnCallback(static fn($repository, $callback) => $callback());
        $this->logService->expects(self::once())->method('getCommitsSince')->with($repository, $since, $limit)->willReturn([$commit]);

        $result = $this->service->getCommitsSince($repository, $since, $limit);
        static::assertSame([$commit], $result);
    }
}

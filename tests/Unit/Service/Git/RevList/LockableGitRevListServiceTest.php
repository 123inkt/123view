<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\RevList;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\Git\GitRepositoryLockManager;
use DR\Review\Service\Git\RevList\GitRevListService;
use DR\Review\Service\Git\RevList\LockableGitRevListService;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(LockableGitRevListService::class)]
class LockableGitRevListServiceTest extends AbstractTestCase
{
    private GitRepositoryLockManager&MockObject $lockManager;
    private GitRevListService&MockObject        $revListService;
    private LockableGitRevListService           $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->lockManager    = $this->createMock(GitRepositoryLockManager::class);
        $this->revListService = $this->createMock(GitRevListService::class);
        $this->service        = new LockableGitRevListService($this->lockManager, $this->revListService);
    }

    /**
     * @throws Exception
     */
    public function testGetCommitsAheadOfMaster(): void
    {
        $repository = new Repository();
        $file       = new DiffFile();

        $this->lockManager->expects($this->once())
            ->method('start')
            ->with($repository)
            ->willReturnCallback(static fn($repository, $callback) => $callback());
        $this->revListService->expects($this->once())->method('getCommitsAheadOfMaster')->with($repository, 'branch')->willReturn([$file]);

        $result = $this->service->getCommitsAheadOfMaster($repository, 'branch');
        static::assertSame([$file], $result);
    }
}

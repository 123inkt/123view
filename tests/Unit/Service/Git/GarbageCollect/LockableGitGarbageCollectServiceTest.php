<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\GarbageCollect;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\Git\GarbageCollect\GitGarbageCollectService;
use DR\Review\Service\Git\GarbageCollect\LockableGitGarbageCollectService;
use DR\Review\Service\Git\GitRepositoryLockManager;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(LockableGitGarbageCollectService::class)]
class LockableGitGarbageCollectServiceTest extends AbstractTestCase
{
    private GitRepositoryLockManager&MockObject $lockManager;
    private GitGarbageCollectService&MockObject $garbageCollectService;
    private LockableGitGarbageCollectService    $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->lockManager           = $this->createMock(GitRepositoryLockManager::class);
        $this->garbageCollectService = $this->createMock(GitGarbageCollectService::class);
        $this->service               = new LockableGitGarbageCollectService($this->lockManager, $this->garbageCollectService);
    }

    /**
     * @throws Exception
     */
    public function testGarbageCollect(): void
    {
        $repository = new Repository();

        $this->lockManager->expects($this->once())
            ->method('start')
            ->with($repository)
            ->willReturnCallback(static fn($repository, $callback) => $callback());
        $this->garbageCollectService->expects($this->once())->method('garbageCollect')->with($repository, 'date');

        $this->service->garbageCollect($repository, 'date');
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Fetch;

use DR\Review\Entity\Git\Fetch\BranchUpdate;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\Git\Fetch\GitFetchService;
use DR\Review\Service\Git\Fetch\LockableGitFetchService;
use DR\Review\Service\Git\GitRepositoryLockManager;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(LockableGitFetchService::class)]
class LockableGitFetchServiceTest extends AbstractTestCase
{
    private GitRepositoryLockManager&MockObject $lockManager;
    private GitFetchService&MockObject          $fetchService;
    private LockableGitFetchService             $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->lockManager  = $this->createMock(GitRepositoryLockManager::class);
        $this->fetchService = $this->createMock(GitFetchService::class);
        $this->service      = new LockableGitFetchService($this->lockManager, $this->fetchService);
    }

    /**
     * @throws Exception
     */
    public function testFetch(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $change = new BranchUpdate('from', 'to', 'oldBranch', 'newBranch');

        $this->lockManager->expects($this->once())
            ->method('start')
            ->with($repository)
            ->willReturnCallback(static fn($repository, $callback) => $callback());
        $this->fetchService->expects($this->once())->method('fetch')->with($repository)->willReturn([$change]);

        $result = $this->service->fetch($repository);
        static::assertSame([$change], $result);
    }
}

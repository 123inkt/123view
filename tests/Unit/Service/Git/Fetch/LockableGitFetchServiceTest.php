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
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Service\Git\Fetch\LockableGitFetchService
 * @covers ::__construct
 */
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
     * @covers ::fetch
     * @throws Exception
     */
    public function testFetch(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $change = new BranchUpdate('from', 'to', 'oldBranch', 'newBranch');

        $this->lockManager->expects(self::once())
            ->method('start')
            ->with($repository)
            ->willReturnCallback(static fn($repository, $callback) => $callback());
        $this->fetchService->expects(self::once())->method('fetch')->with($repository)->willReturn([$change]);

        $result = $this->service->fetch($repository);
        static::assertSame([$change], $result);
    }
}

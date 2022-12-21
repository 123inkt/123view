<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Show;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\Git\GitRepositoryLockManager;
use DR\Review\Service\Git\Show\GitShowService;
use DR\Review\Service\Git\Show\LockableGitShowService;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Service\Git\Show\LockableGitShowService
 * @covers ::__construct
 */
class LockableGitShowServiceTest extends AbstractTestCase
{

    private GitRepositoryLockManager&MockObject $lockManager;
    private GitShowService&MockObject           $showService;
    private LockableGitShowService              $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->lockManager = $this->createMock(GitRepositoryLockManager::class);
        $this->showService = $this->createMock(GitShowService::class);
        $this->service     = new LockableGitShowService($this->lockManager, $this->showService);
    }

    /**
     * @covers ::getCommitFromHash
     * @throws Exception
     */
    public function testGetCommitFromHash(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $commit = $this->createCommit();

        $this->lockManager->expects(self::once())
            ->method('start')
            ->with($repository)
            ->willReturnCallback(static fn($repository, $callback) => $callback());
        $this->showService->expects(self::once())->method('getCommitFromHash')->with($repository, 'hash')->willReturn($commit);

        $result = $this->service->getCommitFromHash($repository, 'hash');
        static::assertSame($commit, $result);
    }
}

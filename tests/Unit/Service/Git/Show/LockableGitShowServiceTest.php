<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Show;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Service\Git\GitRepositoryLockManager;
use DR\Review\Service\Git\Show\GitShowService;
use DR\Review\Service\Git\Show\LockableGitShowService;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(LockableGitShowService::class)]
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
     * @throws Exception
     */
    public function testGetCommitFromHash(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $commit = $this->createCommit();

        $this->lockManager->expects($this->once())
            ->method('start')
            ->with($repository)
            ->willReturnCallback(static fn($repository, $callback) => $callback());
        $this->showService->expects($this->once())->method('getCommitFromHash')->with($repository, 'hash')->willReturn($commit);

        $result = $this->service->getCommitFromHash($repository, 'hash');
        static::assertSame($commit, $result);
    }

    /**
     * @throws Exception
     */
    public function testGetFileContents(): void
    {
        $repository = new Repository();
        $revision   = new Revision();
        $revision->setRepository($repository);

        $this->lockManager->expects($this->once())
            ->method('start')
            ->with($repository)
            ->willReturnCallback(static fn($repository, $callback) => $callback());
        $this->showService->expects($this->once())->method('getFileContents')->with($revision, 'file', true)->willReturn('output');

        static::assertSame('output', $this->service->getFileContents($revision, 'file', true));
    }
}

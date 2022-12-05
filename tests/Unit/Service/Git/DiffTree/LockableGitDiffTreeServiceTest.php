<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\DiffTree;

use DR\GitCommitNotification\Entity\Repository\Repository;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Service\Git\DiffTree\GitDiffTreeService;
use DR\GitCommitNotification\Service\Git\DiffTree\LockableGitDiffTreeService;
use DR\GitCommitNotification\Service\Git\GitRepositoryLockManager;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\DiffTree\LockableGitDiffTreeService
 * @covers ::__construct
 */
class LockableGitDiffTreeServiceTest extends AbstractTestCase
{
    private GitRepositoryLockManager&MockObject $lockManager;
    private GitDiffTreeService&MockObject       $treeService;
    private LockableGitDiffTreeService          $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->lockManager = $this->createMock(GitRepositoryLockManager::class);
        $this->treeService = $this->createMock(GitDiffTreeService::class);
        $this->service     = new LockableGitDiffTreeService($this->lockManager, $this->treeService);
    }

    /**
     * @covers ::getFilesInRevision
     * @throws Exception
     */
    public function testGetFilesInRevision(): void
    {
        $revision   = new Revision();
        $repository = new Repository();
        $revision->setRepository($repository);

        $this->lockManager->expects(self::once())
            ->method('start')
            ->with($repository)
            ->willReturnCallback(static fn($repository, $callback) => $callback());
        $this->treeService->expects(self::once())->method('getFilesInRevision')->with($revision)->willReturn(['file']);

        $result = $this->service->getFilesInRevision($revision);
        static::assertSame(['file'], $result);
    }
}

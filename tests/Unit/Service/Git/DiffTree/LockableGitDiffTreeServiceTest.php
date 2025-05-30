<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\DiffTree;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Service\Git\DiffTree\GitDiffTreeService;
use DR\Review\Service\Git\DiffTree\LockableGitDiffTreeService;
use DR\Review\Service\Git\GitRepositoryLockManager;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(LockableGitDiffTreeService::class)]
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
     * @throws Exception
     */
    public function testGetFilesInRevision(): void
    {
        $revision   = new Revision();
        $repository = new Repository();
        $revision->setRepository($repository);

        $this->lockManager->expects($this->once())
            ->method('start')
            ->with($repository)
            ->willReturnCallback(static fn($repository, $callback) => $callback());
        $this->treeService->expects($this->once())->method('getFilesInRevision')->with($revision)->willReturn(['file']);

        $result = $this->service->getFilesInRevision($revision);
        static::assertSame(['file'], $result);
    }
}

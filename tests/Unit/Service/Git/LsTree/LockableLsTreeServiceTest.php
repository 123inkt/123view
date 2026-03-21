<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\LsTree;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Service\Git\GitRepositoryLockManager;
use DR\Review\Service\Git\LsTree\LockableLsTreeService;
use DR\Review\Service\Git\LsTree\LsTreeService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(LockableLsTreeService::class)]
class LockableLsTreeServiceTest extends AbstractTestCase
{
    private GitRepositoryLockManager&MockObject $lockManager;
    private LsTreeService&MockObject            $lsTreeService;
    private LockableLsTreeService               $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lockManager   = $this->createMock(GitRepositoryLockManager::class);
        $this->lsTreeService = $this->createMock(LsTreeService::class);
        $this->service       = new LockableLsTreeService($this->lockManager, $this->lsTreeService);
    }

    public function testListFiles(): void
    {
        $repository = new Repository();
        $revision   = new Revision();
        $revision->setRepository($repository);

        $expectedFiles = ['file1.txt', 'file2.txt'];

        $this->lockManager->expects($this->once())
            ->method('start')
            ->with($repository, static::isCallable())
            ->willReturnCallback(static fn(Repository $repo, callable $callback) => $callback());

        $this->lsTreeService->expects($this->once())
            ->method('listFiles')
            ->with($revision, 'path/to/files')
            ->willReturn($expectedFiles);

        $result = $this->service->listFiles($revision, 'path/to/files');

        static::assertSame($expectedFiles, $result);
    }
}

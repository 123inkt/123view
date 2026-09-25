<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Grep;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Service\Git\GitRepositoryLockManager;
use DR\Review\Service\Git\Grep\GitGrepService;
use DR\Review\Service\Git\Grep\LockableGitGrepService;
use DR\Review\Tests\AbstractTestCase;
use League\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(LockableGitGrepService::class)]
class LockableGitGrepServiceTest extends AbstractTestCase
{
    private GitRepositoryLockManager&MockObject $lockManager;
    private GitGrepService&MockObject           $grepService;
    private LockableGitGrepService              $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lockManager = $this->createMock(GitRepositoryLockManager::class);
        $this->grepService = $this->createMock(GitGrepService::class);
        $this->service     = new LockableGitGrepService($this->lockManager, $this->grepService);
    }

    public function testGrep(): void
    {
        $repository = new Repository();
        $repository->setUrl(Uri::new('https://url/'));
        
        $revision = new Revision();
        $revision->setRepository($repository);
        $revision->setCommitHash('abc123');

        $this->lockManager->expects($this->once())->method('start')
            ->with($repository)
            ->willReturnCallback(static fn($repository, callable $callback) => $callback());
        $this->grepService->expects($this->once())->method('grep')
            ->with($revision, 'pattern', 5)
            ->willReturn('grep result');

        $result = $this->service->grep($revision, 'pattern', 5);
        static::assertSame('grep result', $result);
    }
}

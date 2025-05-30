<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Branch;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\Git\Branch\CacheableGitBranchService;
use DR\Review\Service\Git\Branch\GitBranchService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Cache\CacheItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Throwable;

#[CoversClass(CacheableGitBranchService::class)]
class CacheableGitBranchServiceTest extends AbstractTestCase
{
    private CacheInterface&MockObject   $cache;
    private GitBranchService&MockObject $branchService;
    private CacheableGitBranchService   $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cache         = $this->createMock(CacheInterface::class);
        $this->branchService = $this->createMock(GitBranchService::class);
        $this->service       = new CacheableGitBranchService($this->cache, $this->branchService);
    }

    /**
     * @throws Throwable
     */
    public function testGetRemoteBranchesWithAllBranches(): void
    {
        $repository = (new Repository())->setId(123);
        $item       = $this->createMock(CacheItemInterface::class);
        $item->expects($this->once())->method('expiresAfter')->with(60);

        $this->cache->expects($this->once())->method('get')
            ->with('git-branch-command-123-all')
            ->willReturnCallback(static fn($key, $callback) => $callback($item));
        $this->branchService->expects($this->once())->method('getRemoteBranches')->with($repository, false)->willReturn(['branch']);

        $branches = $this->service->getRemoteBranches($repository);
        static::assertSame(['branch'], $branches);
    }

    /**
     * @throws Throwable
     */
    public function testGetRemoteBranchesWithMergedOnlyBranches(): void
    {
        $repository = (new Repository())->setId(123);
        $item       = $this->createMock(CacheItemInterface::class);
        $item->expects($this->once())->method('expiresAfter')->with(60);

        $this->cache->expects($this->once())->method('get')
            ->with('git-branch-command-123-merged')
            ->willReturnCallback(static fn($key, $callback) => $callback($item));
        $this->branchService->expects($this->once())->method('getRemoteBranches')->with($repository, true)->willReturn(['branch']);

        $branches = $this->service->getRemoteBranches($repository, true);
        static::assertSame(['branch'], $branches);
    }
}

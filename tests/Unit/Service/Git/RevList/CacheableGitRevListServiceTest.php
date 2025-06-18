<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\RevList;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\Git\RevList\CacheableGitRevListService;
use DR\Review\Service\Git\RevList\LockableGitRevListService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Cache\CacheItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Throwable;

#[CoversClass(CacheableGitRevListService::class)]
class CacheableGitRevListServiceTest extends AbstractTestCase
{
    private CacheInterface&MockObject            $cache;
    private LockableGitRevListService&MockObject $revListService;
    private CacheableGitRevListService           $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->cache          = $this->createMock(CacheInterface::class);
        $this->revListService = $this->createMock(LockableGitRevListService::class);
        $this->service        = new CacheableGitRevListService($this->cache, $this->revListService);
    }

    /**
     * @throws Throwable
     */
    public function testGetCommitsAheadOfMaster(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $cacheItem = $this->createMock(CacheItemInterface::class);
        $file      = new DiffFile();

        $cacheItem->expects($this->once())->method('expiresAfter')->with(60);
        $this->cache->expects($this->once())
            ->method('get')
            ->with('git-rev-list-b14f7f843e816997c6153498d618565448b45f53829a1f79dc60a7a51acd3882')
            ->willReturnCallback(static fn($repository, $callback) => $callback($cacheItem));
        $this->revListService->expects($this->once())->method('getCommitsAheadOf')->with($repository, 'branch', 'target')->willReturn([$file]);

        $result = $this->service->getCommitsAheadOf($repository, 'branch', 'target');
        static::assertSame([$file], $result);
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ExternalTool\Gitlab;

use DR\Review\ExternalTool\Gitlab\GitlabService;
use DR\Review\Service\Api\Gitlab\Branches;
use DR\Review\Service\Api\Gitlab\GitlabApi;
use DR\Review\Service\Api\Gitlab\MergeRequests;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\Cache\CacheInterface;
use Throwable;

#[CoversClass(GitlabService::class)]
class GitlabServiceTest extends AbstractTestCase
{
    private GitlabApi&MockObject      $api;
    private MergeRequests&MockObject  $mergeRequests;
    private Branches&MockObject       $branches;
    private CacheInterface&MockObject $cache;
    private GitlabService             $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mergeRequests = $this->createMock(MergeRequests::class);
        $this->branches      = $this->createMock(Branches::class);
        $this->api           = $this->createMock(GitlabApi::class);
        $this->api->method('mergeRequests')->willReturn($this->mergeRequests);
        $this->api->method('branches')->willReturn($this->branches);
        $this->cache   = $this->createMock(CacheInterface::class);
        $this->service = new GitlabService($this->api, $this->cache);
    }

    /**
     * @throws Throwable
     */
    public function testGetBranchUrl(): void
    {
        $this->cache->expects($this->once())->method('get')
            ->with('branch-url-111-remote-ref')
            ->willReturnCallback(static fn($key, $callback) => $callback());
        $this->branches->expects($this->once())->method('getBranch')->with(111, 'remote-ref')->willReturn(['web_url' => 'url']);

        static::assertSame('url', $this->service->getBranchUrl(111, 'remote-ref'));
    }

    /**
     * @throws Throwable
     */
    public function testGetMergeRequestUrl(): void
    {
        $this->cache->expects($this->once())->method('get')
            ->with('merge-request-url-111-remote-ref')
            ->willReturnCallback(static fn($key, $callback) => $callback());
        $this->mergeRequests->expects($this->once())->method('findByRemoteRef')->with(111, 'remote-ref')->willReturn(['web_url' => 'url']);

        static::assertSame('url', $this->service->getMergeRequestUrl(111, 'remote-ref'));
    }

    /**
     * @throws Throwable
     */
    public function testGetMergeRequestUrl2(): void
    {
        $this->cache->expects($this->once())->method('get')
            ->with('merge-request-url-111-remote-ref')
            ->willReturnCallback(static fn($key, $callback) => $callback());
        $this->mergeRequests->expects($this->once())->method('findByRemoteRef')->with(111, 'remote-ref')->willReturn(['target_branch' => 'branch']);

        static::assertSame('branch', $this->service->getMergeRequestTargetBranch(111, 'remote-ref'));
    }
}

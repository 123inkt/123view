<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\Branch\GitBranchService;
use DR\Review\Service\Git\Checkout\GitCheckoutService;
use DR\Review\Service\Git\CherryPick\GitCherryPickService;
use DR\Review\Service\Git\Clean\GitCleanService;
use DR\Review\Service\Git\GitRepositoryResetManager;
use DR\Review\Service\Git\Reset\GitResetService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(GitRepositoryResetManager::class)]
class GitRepositoryResetManagerTest extends AbstractTestCase
{
    private GitCherryPickService&MockObject $cherryPickService;
    private GitCheckoutService&MockObject   $checkoutService;
    private GitResetService&MockObject      $resetService;
    private GitBranchService&MockObject     $branchService;
    private GitCleanService&MockObject      $cleanService;
    private GitRepositoryResetManager       $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->cherryPickService = $this->createMock(GitCherryPickService::class);
        $this->checkoutService   = $this->createMock(GitCheckoutService::class);
        $this->resetService      = $this->createMock(GitResetService::class);
        $this->branchService     = $this->createMock(GitBranchService::class);
        $this->cleanService      = $this->createMock(GitCleanService::class);
        $this->service           = new GitRepositoryResetManager(
            $this->cherryPickService,
            $this->checkoutService,
            $this->resetService,
            $this->branchService,
            $this->cleanService
        );
    }

    /**
     * @throws RepositoryException
     */
    public function testStart(): void
    {
        $repository = new Repository();
        $branchName = 'branch';

        $this->cherryPickService->expects($this->once())->method('tryCherryPickAbort')->with($repository)->willReturn(true);
        $this->resetService->expects($this->once())->method('resetHard')->with($repository);
        $this->cleanService->expects($this->once())->method('forceClean')->with($repository);
        $this->checkoutService->expects($this->once())->method('checkout')->with($repository, 'master');
        $this->branchService->expects($this->once())->method('tryDeleteBranch')->with($repository, $branchName);

        static::assertTrue($this->service->start($repository, $branchName, static fn() => true));
    }
}

<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git;

use DR\GitCommitNotification\Entity\Repository\Repository;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Service\Git\Branch\GitBranchService;
use DR\GitCommitNotification\Service\Git\Checkout\GitCheckoutService;
use DR\GitCommitNotification\Service\Git\GitRepositoryResetManager;
use DR\GitCommitNotification\Service\Git\Reset\GitResetService;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\GitRepositoryResetManager
 * @covers ::__construct
 */
class GitRepositoryResetManagerTest extends AbstractTestCase
{
    private GitCheckoutService&MockObject $checkoutService;
    private GitResetService&MockObject    $resetService;
    private GitBranchService&MockObject   $branchService;
    private GitRepositoryResetManager     $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->checkoutService = $this->createMock(GitCheckoutService::class);
        $this->resetService    = $this->createMock(GitResetService::class);
        $this->branchService   = $this->createMock(GitBranchService::class);
        $this->service         = new GitRepositoryResetManager($this->checkoutService, $this->resetService, $this->branchService);
    }

    /**
     * @covers ::start
     * @throws RepositoryException
     */
    public function testStart(): void
    {
        $repository = new Repository();
        $branchName = 'branch';

        $this->resetService->expects(self::once())->method('resetHard')->with($repository);
        $this->checkoutService->expects(self::once())->method('checkout')->with($repository, 'master');
        $this->branchService->expects(self::once())->method('tryDeleteBranch')->with($repository, $branchName);

        static::assertTrue($this->service->start($repository, $branchName, static fn() => true));
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Review\Strategy;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Exception\ParseException;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\Checkout\GitCheckoutService;
use DR\Review\Service\Git\CherryPick\GitCherryPickService;
use DR\Review\Service\Git\Diff\GitDiffService;
use DR\Review\Service\Git\GitRepositoryResetManager;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Review\Strategy\BasicCherryPickStrategy;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Service\Git\Review\Strategy\BasicCherryPickStrategy
 * @covers ::__construct
 */
class BasicCherryPickStrategyTest extends AbstractTestCase
{
    private GitCheckoutService&MockObject        $checkoutService;
    private GitCherryPickService&MockObject      $cherryPickService;
    private GitDiffService&MockObject            $diffService;
    private GitRepositoryResetManager&MockObject $resetManager;
    private BasicCherryPickStrategy              $strategy;

    public function setUp(): void
    {
        parent::setUp();
        $this->checkoutService   = $this->createMock(GitCheckoutService::class);
        $this->cherryPickService = $this->createMock(GitCherryPickService::class);
        $this->diffService       = $this->createMock(GitDiffService::class);
        $this->resetManager      = $this->createMock(GitRepositoryResetManager::class);
        $this->strategy          = new BasicCherryPickStrategy(
            $this->checkoutService,
            $this->cherryPickService,
            $this->diffService,
            $this->resetManager
        );
    }

    /**
     * @covers ::getDiffFiles
     * @throws RepositoryException|ParseException
     */
    public function testGetDiffFilesSuccessful(): void
    {
        $repository = new Repository();
        $revision   = new Revision();
        $diffFile   = new DiffFile();
        $branchName = 'branchName';

        $this->checkoutService->expects(self::once())->method('checkoutRevision')->with($revision)->willReturn($branchName);
        $this->resetManager->expects(self::once())
            ->method('start')
            ->with($repository, $branchName)
            ->willReturnCallback(static fn($repository, $branchName, $callback) => $callback());
        $this->cherryPickService->expects(self::once())->method('cherryPickRevisions')->with([$revision]);
        $this->diffService->expects(self::once())->method('getBundledDiffFromRevisions')->with($repository)->willReturn([$diffFile]);

        $this->strategy->getDiffFiles($repository, [$revision], new FileDiffOptions(20));
    }

    /**
     * @covers ::getDiffFiles
     * @throws RepositoryException|ParseException
     */
    public function testGetDiffFilesFailure(): void
    {
        $repository = new Repository();
        $revision   = new Revision();
        $branchName = 'branchName';

        $this->checkoutService->expects(self::once())->method('checkoutRevision')->with($revision)->willReturn($branchName);
        $this->resetManager->expects(self::once())
            ->method('start')
            ->with($repository, $branchName)
            ->willReturnCallback(static fn($repository, $branchName, $callback) => $callback());
        $this->cherryPickService->expects(self::once())
            ->method('cherryPickRevisions')
            ->with([$revision])
            ->willThrowException(new RepositoryException());
        $this->cherryPickService->expects(self::once())->method('cherryPickAbort')->with($repository);

        $this->expectException(RepositoryException::class);
        $this->strategy->getDiffFiles($repository, [$revision]);
    }
}

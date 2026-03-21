<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Review\Strategy;

use DR\Review\Entity\Git\CherryPick\CherryPickResult;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\Checkout\RecoverableGitCheckoutService;
use DR\Review\Service\Git\CherryPick\GitCherryPickService;
use DR\Review\Service\Git\Diff\GitDiffService;
use DR\Review\Service\Git\GitRepositoryResetManager;
use DR\Review\Service\Git\Review\Strategy\BasicCherryPickStrategy;
use DR\Review\Service\Git\Review\Strategy\HesitantCherryPickStrategy;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(HesitantCherryPickStrategy::class)]
class HesitantCherryPickStrategyTest extends AbstractTestCase
{
    private RecoverableGitCheckoutService&MockObject        $checkoutService;
    private GitCherryPickService&MockObject      $cherryPickService;
    private GitDiffService&MockObject            $diffService;
    private GitRepositoryResetManager&MockObject $resetManager;
    private BasicCherryPickStrategy&MockObject   $cherryPickStrategy;
    private HesitantCherryPickStrategy           $strategy;

    public function setUp(): void
    {
        parent::setUp();
        $this->checkoutService    = $this->createMock(RecoverableGitCheckoutService::class);
        $this->cherryPickService  = $this->createMock(GitCherryPickService::class);
        $this->diffService        = $this->createMock(GitDiffService::class);
        $this->resetManager       = $this->createMock(GitRepositoryResetManager::class);
        $this->cherryPickStrategy = $this->createMock(BasicCherryPickStrategy::class);
        $this->strategy           = new HesitantCherryPickStrategy(
            $this->checkoutService,
            $this->cherryPickService,
            $this->diffService,
            $this->resetManager,
            $this->cherryPickStrategy
        );
    }

    /**
     * @throws Throwable
     */
    public function testGetDiffFilesSingleRevision(): void
    {
        $repository = new Repository();
        $revision   = new Revision();
        $diffFile   = new DiffFile();

        $this->diffService->expects($this->once())->method('getDiffFromRevision')->with($revision)->willReturn([$diffFile]);
        $this->checkoutService->expects($this->never())->method('checkoutRevision');
        $this->cherryPickService->expects($this->never())->method('cherryPickRevisions');
        $this->resetManager->expects($this->never())->method('start');
        $this->cherryPickStrategy->expects($this->never())->method('getDiffFiles');

        static::assertSame([$diffFile], $this->strategy->getDiffFiles($repository, [$revision]));
    }

    /**
     * @throws Throwable
     */
    public function testGetDiffFilesTwoSuccessfulRevisions(): void
    {
        $repository = new Repository();
        $revisionA  = new Revision();
        $revisionB  = new Revision();
        $revisions  = [$revisionA, $revisionB];
        $diffFile   = new DiffFile();

        $this->checkoutService->expects($this->once())->method('checkoutRevision')->with($revisionA)->willReturn('branchName');
        $this->cherryPickService->expects($this->exactly(2))
            ->method('cherryPickRevisions')
            ->with(...consecutive([[$revisionA]], [[$revisionB]]));
        $this->resetManager->expects($this->once())
            ->method('start')
            ->with($repository, 'branchName')
            // phpcs:disable
            ->willReturnCallback(static fn($repository, $branchName, $callback) => $callback());
        // phpcs:enable
        $this->cherryPickStrategy->expects($this->once())->method('getDiffFiles')->with($repository, $revisions)->willReturn([$diffFile]);
        $this->diffService->expects($this->never())->method('getDiffFromRevision');

        static::assertSame([$diffFile], $this->strategy->getDiffFiles($repository, $revisions));
    }

    /**
     * @throws Throwable
     */
    public function testGetDiffFilesTwoUnsuccessfulRevisions(): void
    {
        $repository = new Repository();
        $revisionA  = new Revision();
        $revisionB  = new Revision();
        $revisions  = [$revisionA, $revisionB];
        $diffFileA  = new DiffFile();
        $diffFileB  = new DiffFile();

        $this->checkoutService->expects($this->once())->method('checkoutRevision')->with($revisionA)->willReturn('branchName');

        // trigger exception on the second cherry-pick
        $this->cherryPickService->expects($this->exactly(2))
            ->method('cherryPickRevisions')
            ->with(...consecutive([[$revisionA]], [[$revisionB]]))
            ->willReturnOnConsecutiveCalls(new CherryPickResult(true), static::throwException(new RepositoryException()));
        $this->resetManager->expects($this->once())
            ->method('start')
            ->with($repository, 'branchName')
            ->willReturnCallback(static fn($repository, $branchName, $callback) => $callback());

        // revisionA will get fetched via tryCherryPick
        $this->cherryPickStrategy->expects($this->once())->method('getDiffFiles')->with($repository, [$revisionA])->willReturn([$diffFileA]);
        // revisionB will get fetched via getDiffFromRevision
        $this->diffService->expects($this->once())->method('getDiffFromRevision')->with($revisionB)->willReturn([$diffFileB]);

        static::assertSame([$diffFileA, $diffFileB], $this->strategy->getDiffFiles($repository, $revisions));
    }
}

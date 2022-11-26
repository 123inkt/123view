<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\Review\Strategy;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Repository\Repository;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Service\Git\Branch\GitBranchService;
use DR\GitCommitNotification\Service\Git\Checkout\GitCheckoutService;
use DR\GitCommitNotification\Service\Git\CherryPick\GitCherryPickService;
use DR\GitCommitNotification\Service\Git\Diff\GitDiffService;
use DR\GitCommitNotification\Service\Git\Reset\GitResetService;
use DR\GitCommitNotification\Service\Git\Review\Strategy\BasicCherryPickStrategy;
use DR\GitCommitNotification\Service\Git\Review\Strategy\HesitantCherryPickStrategy;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\Review\Strategy\HesitantCherryPickStrategy
 * @covers ::__construct
 */
class HesitantCherryPickStrategyTest extends AbstractTestCase
{
    private GitCheckoutService&MockObject      $checkoutService;
    private GitCherryPickService&MockObject    $cherryPickService;
    private GitDiffService&MockObject          $diffService;
    private GitResetService&MockObject         $resetService;
    private GitBranchService&MockObject        $branchService;
    private BasicCherryPickStrategy&MockObject $cherryPickStrategy;
    private HesitantCherryPickStrategy         $strategy;

    public function setUp(): void
    {
        parent::setUp();
        $this->checkoutService    = $this->createMock(GitCheckoutService::class);
        $this->cherryPickService  = $this->createMock(GitCherryPickService::class);
        $this->diffService        = $this->createMock(GitDiffService::class);
        $this->resetService       = $this->createMock(GitResetService::class);
        $this->branchService      = $this->createMock(GitBranchService::class);
        $this->cherryPickStrategy = $this->createMock(BasicCherryPickStrategy::class);
        $this->strategy           = new HesitantCherryPickStrategy(
            $this->checkoutService,
            $this->cherryPickService,
            $this->diffService,
            $this->resetService,
            $this->branchService,
            $this->cherryPickStrategy
        );
    }

    /**
     * @covers ::getDiffFiles
     * @throws Throwable
     */
    public function testGetDiffFilesSingleRevision(): void
    {
        $repository = new Repository();
        $revision   = new Revision();
        $diffFile   = new DiffFile();

        $this->diffService->expects(self::once())->method('getDiffFromRevision')->with($revision)->willReturn([$diffFile]);

        static::assertSame([$diffFile], $this->strategy->getDiffFiles($repository, [$revision]));
    }

    /**
     * @covers ::getDiffFiles
     * @covers ::tryCherryPick
     * @throws Throwable
     */
    public function testGetDiffFilesTwoSuccessfulRevisions(): void
    {
        $repository = new Repository();
        $revisionA  = new Revision();
        $revisionB  = new Revision();
        $revisions  = [$revisionA, $revisionB];
        $diffFile   = new DiffFile();

        $this->checkoutService->expects(self::once())->method('checkoutRevision')->with($revisionA)->willReturn('branchName');
        $this->cherryPickService->expects(self::exactly(2))->method('cherryPickRevisions')->withConsecutive([[$revisionA]], [[$revisionB]]);
        $this->resetService->expects(self::once())->method('resetHard')->with($repository);
        $this->checkoutService->expects(self::once())->method('checkout')->with($repository, 'master');
        $this->branchService->expects(self::once())->method('tryDeleteBranch')->with($repository, 'branchName');
        $this->cherryPickStrategy->expects(self::once())->method('getDiffFiles')->with($repository, $revisions)->willReturn([$diffFile]);

        static::assertSame([$diffFile], $this->strategy->getDiffFiles($repository, $revisions));
    }

    /**
     * @covers ::getDiffFiles
     * @covers ::tryCherryPick
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

        $this->checkoutService->expects(self::once())->method('checkoutRevision')->with($revisionA)->willReturn('branchName');

        // trigger exception on the second cherry-pick
        $this->cherryPickService->expects(self::exactly(2))
            ->method('cherryPickRevisions')
            ->will(static::onConsecutiveCalls([$revisionA], static::throwException(new RepositoryException())));

        $this->resetService->expects(self::once())->method('resetHard')->with($repository);
        $this->checkoutService->expects(self::once())->method('checkout')->with($repository, 'master');
        $this->branchService->expects(self::once())->method('tryDeleteBranch')->with($repository, 'branchName');

        // revisionA will get fetched via tryCherryPick
        $this->cherryPickStrategy->expects(self::once())->method('getDiffFiles')->with($repository, [$revisionA])->willReturn([$diffFileA]);
        // revisionB will get fetched via getDiffFromRevision
        $this->diffService->expects(self::once())->method('getDiffFromRevision')->with($revisionB)->willReturn([$diffFileB]);

        static::assertSame([$diffFileA, $diffFileB], $this->strategy->getDiffFiles($repository, $revisions));
    }
}

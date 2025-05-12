<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Review\Strategy;

use DR\Review\Entity\Git\CherryPick\CherryPickResult;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Exception\ParseException;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\Add\GitAddService;
use DR\Review\Service\Git\Checkout\RecoverableGitCheckoutService;
use DR\Review\Service\Git\CherryPick\GitCherryPickService;
use DR\Review\Service\Git\Commit\GitCommitService;
use DR\Review\Service\Git\Diff\GitDiffService;
use DR\Review\Service\Git\GitRepositoryResetManager;
use DR\Review\Service\Git\Reset\GitResetService;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Review\Strategy\PersistentCherryPickStrategy;
use DR\Review\Service\Git\Status\GitStatusService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(PersistentCherryPickStrategy::class)]
class PersistentCherryPickStrategyTest extends AbstractTestCase
{
    private GitAddService&MockObject                 $addService;
    private GitStatusService&MockObject              $statusService;
    private GitCommitService&MockObject              $commitService;
    private RecoverableGitCheckoutService&MockObject $checkoutService;
    private GitCherryPickService&MockObject          $cherryPickService;
    private GitResetService&MockObject               $resetService;
    private GitDiffService&MockObject                $diffService;
    private GitRepositoryResetManager&MockObject     $resetManager;
    private PersistentCherryPickStrategy             $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->addService        = $this->createMock(GitAddService::class);
        $this->statusService     = $this->createMock(GitStatusService::class);
        $this->commitService     = $this->createMock(GitCommitService::class);
        $this->checkoutService   = $this->createMock(RecoverableGitCheckoutService::class);
        $this->cherryPickService = $this->createMock(GitCherryPickService::class);
        $this->resetService      = $this->createMock(GitResetService::class);
        $this->diffService       = $this->createMock(GitDiffService::class);
        $this->resetManager      = $this->createMock(GitRepositoryResetManager::class);
        $this->strategy          = new PersistentCherryPickStrategy(
            $this->addService,
            $this->statusService,
            $this->commitService,
            $this->checkoutService,
            $this->cherryPickService,
            $this->resetService,
            $this->diffService,
            $this->resetManager,
        );
    }

    /**
     * @throws RepositoryException|ParseException
     */
    public function testGetDiffFiles(): void
    {
        $repository = new Repository();
        $revision   = new Revision();
        $revision->setCommitHash('hash');
        $options             = new FileDiffOptions(5, DiffComparePolicy::TRIM);
        $file                = new DiffFile();
        $file->filePathAfter = 'conflict-file';

        $cherryPickResultA = new CherryPickResult(false, ['conflict-file']);
        $cherryPickResultB = new CherryPickResult(true);

        $this->checkoutService->expects(self::once())->method('checkoutRevision')->with($revision)->willReturn('branch');
        $this->resetManager->expects(self::once())
            ->method('start')
            ->with($repository, 'branch')
            ->willReturnCallback(static fn($repository, $branchName, $callback) => $callback());
        $this->cherryPickService->expects(self::once())->method('cherryPickRevisions')->with([$revision])->willReturn($cherryPickResultA);
        $this->cherryPickService->expects(self::once())->method('cherryPickContinue')->with($repository)->willReturn($cherryPickResultB);
        $this->statusService->expects(self::once())->method('getModifiedFiles')->with()->willReturn(['modified-file']);
        $this->addService->expects(self::once())->method('add')->with($repository, 'modified-file');
        $this->commitService->expects(self::once())->method('commit')->with($repository);
        $this->resetService->expects(self::once())->method('resetSoft')->with($repository, 'hash~');
        $this->diffService->expects(self::once())->method('getBundledDiffFromRevisions')->with($repository, $options)->willReturn([$file]);

        $files = $this->strategy->getDiffFiles($repository, [$revision], $options);
        static::assertSame([$file], $files);
        static::assertTrue($file->hasMergeConflict);
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Fetch;

use DR\Review\Entity\Git\Fetch\BranchCreation;
use DR\Review\Entity\Git\Fetch\BranchUpdate;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Service\Git\Fetch\GitFetchRemoteRevisionService;
use DR\Review\Service\Git\Fetch\LockableGitFetchService;
use DR\Review\Service\Git\Log\LockableGitLogService;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

#[CoversClass(GitFetchRemoteRevisionService::class)]
class GitFetchRemoteRevisionServiceTest extends AbstractTestCase
{
    private LockableGitLogService&MockObject   $logService;
    private LockableGitFetchService&MockObject $fetchService;
    private GitFetchRemoteRevisionService      $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->logService   = $this->createMock(LockableGitLogService::class);
        $this->fetchService = $this->createMock(LockableGitFetchService::class);
        $this->service      = new GitFetchRemoteRevisionService($this->logService, $this->fetchService);
        $this->service->setLogger(static::createStub(LoggerInterface::class));
    }

    /**
     * @throws Exception
     */
    public function testFetchRevisionFromRemoteBranchUpdate(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $repository->setName('name');
        $change   = new BranchUpdate('from', 'to', 'oldBranch', 'newBranch');
        $commit   = $this->createCommit();
        $revision = new Revision();
        $revision->setCreateTimestamp(time());

        $this->fetchService->expects($this->once())->method('fetch')->with($repository)->willReturn([$change]);
        $this->logService->expects($this->once())->method('getCommitsFromRange')->with($repository, 'from', 'to')->willReturn([$commit]);

        $result = $this->service->fetchRevisionFromRemote($repository);

        static::assertSame([$commit], $result);
    }

    /**
     * @throws Exception
     */
    public function testFetchRevisionFromRemoteBranchCreation(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $repository->setName('name');
        $repository->setMainBranchName('main');
        $change   = new BranchCreation('local/newBranch', 'origin/newBranch');
        $commit   = $this->createCommit();
        $revision = new Revision();
        $revision->setCreateTimestamp(time());

        $this->fetchService->expects($this->once())->method('fetch')->with($repository)->willReturn([$change]);
        $this->logService->expects($this->once())
            ->method('getCommitsFromRange')
            ->with($repository, 'origin/main', 'origin/newBranch')
            ->willReturn([$commit]);

        $result = $this->service->fetchRevisionFromRemote($repository);

        static::assertSame([$commit], $result);
    }
}

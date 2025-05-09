<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Message\Revision\FetchRepositoryRevisionsMessage;
use DR\Review\MessageHandler\FetchRepositoryRevisionsMessageHandler;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Service\Git\GitRepositoryLockManager;
use DR\Review\Service\Git\Reset\GitResetService;
use DR\Review\Service\Revision\RevisionFetchService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

#[CoversClass(FetchRepositoryRevisionsMessageHandler::class)]
class FetchRepositoryRevisionsMessageHandlerTest extends AbstractTestCase
{
    private RepositoryRepository&MockObject        $repositoryRepository;
    private RevisionFetchService&MockObject        $remoteRevisionService;
    private GitRepositoryLockManager&MockObject    $lockManager;
    private GitResetService&MockObject             $resetService;
    private FetchRepositoryRevisionsMessageHandler $handler;

    public function setUp(): void
    {
        parent::setUp();
        $this->repositoryRepository  = $this->createMock(RepositoryRepository::class);
        $this->remoteRevisionService = $this->createMock(RevisionFetchService::class);
        $this->lockManager           = $this->createMock(GitRepositoryLockManager::class);
        $this->resetService          = $this->createMock(GitResetService::class);
        $this->handler               = new FetchRepositoryRevisionsMessageHandler(
            $this->repositoryRepository,
            $this->remoteRevisionService,
            $this->lockManager,
            $this->resetService
        );
        $this->handler->setLogger($this->createMock(LoggerInterface::class));
    }

    /**
     * @throws Throwable
     */
    public function testInvokeShouldStopIfRepositoryIsUnknown(): void
    {
        $message = new FetchRepositoryRevisionsMessage(123);
        $this->repositoryRepository->expects($this->once())->method('find')->with(123)->willReturn(null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expecting value to be not null');

        ($this->handler)($message);
    }

    /**
     * @throws Throwable
     */
    public function testInvokeShouldProcessCommits(): void
    {
        $message    = new FetchRepositoryRevisionsMessage(123);
        $repository = new Repository();
        $repository->setId(456);
        $repository->setMainBranchName('master');

        $this->repositoryRepository->expects($this->once())->method('find')->with(123)->willReturn($repository);
        $this->remoteRevisionService->expects($this->once())->method('fetchRevisions')->with($repository);

        $this->lockManager->expects($this->once())->method('start')->willReturnCallback(static fn($repository, callable $callback) => $callback());
        $this->resetService->expects($this->once())->method('resetHard')->with($repository, 'origin/master');

        ($this->handler)($message);
    }
}

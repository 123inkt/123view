<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\MessageHandler;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Message\Revision\FetchRepositoryRevisionsMessage;
use DR\GitCommitNotification\Message\Revision\NewRevisionMessage;
use DR\GitCommitNotification\MessageHandler\FetchRepositoryRevisionsMessageHandler;
use DR\GitCommitNotification\Repository\Config\RepositoryRepository;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;
use DR\GitCommitNotification\Service\Git\Log\LockableGitLogService;
use DR\GitCommitNotification\Service\Revision\RevisionFactory;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

/**
 * @coversDefaultClass \DR\GitCommitNotification\MessageHandler\FetchRepositoryRevisionsMessageHandler
 * @covers ::__construct
 */
class FetchRepositoryRevisionsMessageHandlerTest extends AbstractTestCase
{
    private RepositoryRepository&MockObject        $repositoryRepository;
    private LockableGitLogService&MockObject       $logService;
    private RevisionRepository&MockObject          $revisionRepository;
    private RevisionFactory&MockObject             $revisionFactory;
    private MessageBusInterface&MockObject         $bus;
    private Envelope                               $envelope;
    private FetchRepositoryRevisionsMessageHandler $handler;

    public function setUp(): void
    {
        parent::setUp();
        $this->envelope             = new Envelope(new stdClass(), []);
        $this->repositoryRepository = $this->createMock(RepositoryRepository::class);
        $this->logService           = $this->createMock(LockableGitLogService::class);
        $this->revisionRepository   = $this->createMock(RevisionRepository::class);
        $this->revisionFactory      = $this->createMock(RevisionFactory::class);
        $this->bus                  = $this->createMock(MessageBusInterface::class);
        $this->handler              = new FetchRepositoryRevisionsMessageHandler(
            $this->repositoryRepository,
            $this->logService,
            $this->revisionRepository,
            $this->revisionFactory,
            $this->bus,
            1
        );
        $this->handler->setLogger($this->createMock(LoggerInterface::class));
    }

    /**
     * @covers ::__invoke
     * @throws Throwable
     */
    public function testInvokeShouldStopIfRepositoryIsUnknown(): void
    {
        $message = new FetchRepositoryRevisionsMessage(123);
        $this->repositoryRepository->expects(self::once())->method('find')->with(123)->willReturn(null);

        ($this->handler)($message);
    }

    /**
     * @covers ::__invoke
     * @throws Throwable
     */
    public function testInvokeShouldStopIfThereAreNoCommits(): void
    {
        $message    = new FetchRepositoryRevisionsMessage(123);
        $repository = new Repository();
        $repository->setId(456);
        $revision = new Revision();

        $this->repositoryRepository->expects(self::once())->method('find')->with(123)->willReturn($repository);
        $this->revisionRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['repository' => 456], ['createTimestamp' => 'DESC'])
            ->willReturn($revision);
        $this->logService->expects(self::once())->method('getCommitsSince')->with($repository, $revision, 1000)->willReturn([]);

        ($this->handler)($message);
    }

    /**
     * @covers ::__invoke
     * @covers ::dispatchRevisions
     * @throws Throwable
     */
    public function testInvokeShouldProcessCommits(): void
    {
        $message    = new FetchRepositoryRevisionsMessage(123);
        $repository = new Repository();
        $repository->setId(456);
        $latestRevision = new Revision();
        $newRevision    = new Revision();
        $commit         = $this->createCommit();

        $this->repositoryRepository->expects(self::once())->method('find')->with(123)->willReturn($repository);
        $this->revisionRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['repository' => 456], ['createTimestamp' => 'DESC'])
            ->willReturn($latestRevision);
        $this->logService->expects(self::once())->method('getCommitsSince')->with($repository, $latestRevision, 1000)->willReturn([$commit]);
        $this->revisionFactory->expects(self::once())->method('createFromCommits')->with([$commit])->willReturn([$newRevision]);
        $this->revisionRepository->expects(self::once())->method('saveAll')->with($repository, [$newRevision]);
        $this->bus->expects(self::exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [self::isInstanceOf(NewRevisionMessage::class)],
                [self::isInstanceOf(FetchRepositoryRevisionsMessage::class)]
            )
            ->willReturn($this->envelope);

        ($this->handler)($message);
    }
}

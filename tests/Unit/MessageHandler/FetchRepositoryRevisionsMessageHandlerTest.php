<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\Revision;
use DR\Review\Message\Revision\FetchRepositoryRevisionsMessage;
use DR\Review\Message\Revision\NewRevisionMessage;
use DR\Review\MessageHandler\FetchRepositoryRevisionsMessageHandler;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Review\RevisionRepository;
use DR\Review\Service\Git\Fetch\GitFetchRemoteRevisionService;
use DR\Review\Service\Revision\RevisionFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use RuntimeException;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

/**
 * @coversDefaultClass \DR\Review\MessageHandler\FetchRepositoryRevisionsMessageHandler
 * @covers ::__construct
 */
class FetchRepositoryRevisionsMessageHandlerTest extends AbstractTestCase
{
    private RepositoryRepository&MockObject          $repositoryRepository;
    private GitFetchRemoteRevisionService&MockObject $remoteRevisionService;
    private RevisionRepository&MockObject            $revisionRepository;
    private RevisionFactory&MockObject               $revisionFactory;
    private MessageBusInterface&MockObject           $bus;
    private Envelope                                 $envelope;
    private FetchRepositoryRevisionsMessageHandler   $handler;

    public function setUp(): void
    {
        parent::setUp();
        $this->envelope              = new Envelope(new stdClass(), []);
        $this->repositoryRepository  = $this->createMock(RepositoryRepository::class);
        $this->remoteRevisionService = $this->createMock(GitFetchRemoteRevisionService::class);
        $this->revisionRepository    = $this->createMock(RevisionRepository::class);
        $this->revisionFactory       = $this->createMock(RevisionFactory::class);
        $this->bus                   = $this->createMock(MessageBusInterface::class);
        $this->handler               = new FetchRepositoryRevisionsMessageHandler(
            $this->repositoryRepository,
            $this->remoteRevisionService,
            $this->revisionRepository,
            $this->revisionFactory,
            $this->bus
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

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expecting value to be not null');

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

        $this->repositoryRepository->expects(self::once())->method('find')->with(123)->willReturn($repository);
        $this->remoteRevisionService->expects(self::once())
            ->method('fetchRevisionFromRemote')
            ->with($repository)
            ->willReturn([]);
        $this->revisionFactory->expects(self::never())->method('createFromCommits');

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
        $latestRevision->setCreateTimestamp(14400);
        $newRevision = new Revision();
        $commit      = $this->createCommit();

        $this->repositoryRepository->expects(self::once())->method('find')->with(123)->willReturn($repository);
        $this->remoteRevisionService->expects(self::once())
            ->method('fetchRevisionFromRemote')
            ->with($repository)
            ->willReturn([$commit]);

        $this->revisionFactory->expects(self::once())->method('createFromCommit')->with($commit)->willReturn([$newRevision]);
        $this->revisionRepository->expects(self::once())->method('saveAll')->with($repository, [$newRevision])->willReturn([$newRevision]);
        $this->bus->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(NewRevisionMessage::class))
            ->willReturn($this->envelope);

        ($this->handler)($message);
    }
}

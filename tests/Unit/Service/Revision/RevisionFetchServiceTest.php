<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Revision;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Message\Revision\FetchRepositoryRevisionsMessage;
use DR\Review\Message\Revision\NewRevisionMessage;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\Git\Fetch\GitFetchRemoteRevisionService;
use DR\Review\Service\Revision\RevisionFactory;
use DR\Review\Service\Revision\RevisionFetchService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use RuntimeException;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

#[CoversClass(RevisionFetchService::class)]
class RevisionFetchServiceTest extends AbstractTestCase
{
    private GitFetchRemoteRevisionService&MockObject $remoteRevisionService;
    private RevisionRepository&MockObject            $revisionRepository;
    private RevisionFactory&MockObject               $revisionFactory;
    private MessageBusInterface&MockObject           $bus;
    private Envelope                                 $envelope;
    private RevisionFetchService                     $fetchService;

    public function setUp(): void
    {
        parent::setUp();
        $this->envelope              = new Envelope(new stdClass(), []);
        $this->remoteRevisionService = $this->createMock(GitFetchRemoteRevisionService::class);
        $this->revisionRepository    = $this->createMock(RevisionRepository::class);
        $this->revisionFactory       = $this->createMock(RevisionFactory::class);
        $this->bus                   = $this->createMock(MessageBusInterface::class);
        $this->fetchService          = new RevisionFetchService(
            $this->remoteRevisionService,
            $this->revisionRepository,
            $this->revisionFactory,
            $this->bus
        );
        $this->fetchService->setLogger($this->createMock(LoggerInterface::class));
    }

    /**
     * @throws Throwable
     */
    public function testInvokeShouldStopIfRepositoryIsUnknown(): void
    {
        $message = new FetchRepositoryRevisionsMessage(123);
        $this->repositoryRepository->expects(self::once())->method('find')->with(123)->willReturn(null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expecting value to be not null');

        ($this->fetchService)($message);
    }

    /**
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

        ($this->fetchService)($message);
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Message\Revision\FetchRepositoryRevisionsMessage;
use DR\Review\MessageHandler\FetchRepositoryRevisionsMessageHandler;
use DR\Review\Repository\Config\RepositoryRepository;
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
    private FetchRepositoryRevisionsMessageHandler $handler;

    public function setUp(): void
    {
        parent::setUp();
        $this->repositoryRepository  = $this->createMock(RepositoryRepository::class);
        $this->remoteRevisionService = $this->createMock(RevisionFetchService::class);
        $this->handler               = new FetchRepositoryRevisionsMessageHandler(
            $this->repositoryRepository,
            $this->remoteRevisionService,
        );
        $this->handler->setLogger($this->createMock(LoggerInterface::class));
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

        $this->repositoryRepository->expects(self::once())->method('find')->with(123)->willReturn($repository);
        $this->remoteRevisionService->expects(self::once())->method('fetchRevisions')->with($repository);

        ($this->handler)($message);
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Message\Revision\CommitAddedMessage;
use DR\Review\Message\Revision\NewRevisionMessage;
use DR\Review\MessageHandler\CommitAddedMessageHandler;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\Git\Show\LockableGitShowService;
use DR\Review\Service\Revision\RevisionFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

/**
 * @coversDefaultClass \DR\Review\MessageHandler\CommitAddedMessageHandler
 * @covers ::__construct
 */
class CommitAddedMessageHandlerTest extends AbstractTestCase
{
    private LockableGitShowService&MockObject $showService;
    private RepositoryRepository&MockObject   $repositoryRepository;
    private RevisionRepository&MockObject     $revisionRepository;
    private RevisionFactory&MockObject        $revisionFactory;
    private MessageBusInterface&MockObject    $bus;
    private CommitAddedMessageHandler         $messageHandler;
    private Envelope                          $envelope;

    public function setUp(): void
    {
        parent::setUp();
        $this->envelope             = new Envelope(new stdClass(), []);
        $this->showService          = $this->createMock(LockableGitShowService::class);
        $this->repositoryRepository = $this->createMock(RepositoryRepository::class);
        $this->revisionRepository   = $this->createMock(RevisionRepository::class);
        $this->revisionFactory      = $this->createMock(RevisionFactory::class);
        $this->bus                  = $this->createMock(MessageBusInterface::class);
        $this->messageHandler       = new CommitAddedMessageHandler(
            $this->showService,
            $this->repositoryRepository,
            $this->revisionRepository,
            $this->revisionFactory,
            $this->bus
        );
    }

    /**
     * @covers ::__invoke
     * @throws Throwable
     */
    public function testInvokeAbsentCommit(): void
    {
        $repository = new Repository();
        $repository->setId(123);

        $this->repositoryRepository->expects(self::once())->method('find')->with(123)->willReturn($repository);
        $this->showService->expects(self::once())->method('getCommitFromHash')->with($repository, 'hash')->willReturn(null);
        $this->revisionFactory->expects(self::never())->method('createFromCommit');

        ($this->messageHandler)(new CommitAddedMessage(123, 'hash'));
    }

    /**
     * @covers ::__invoke
     * @throws Throwable
     */
    public function testInvoke(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $commit   = $this->createCommit();
        $revision = new Revision();
        $revision->setId(456);

        $this->repositoryRepository->expects(self::once())->method('find')->with(123)->willReturn($repository);
        $this->showService->expects(self::once())->method('getCommitFromHash')->with($repository, 'hash')->willReturn($commit);
        $this->revisionFactory->expects(self::once())->method('createFromCommit')->with($commit)->willReturn([$revision]);
        $this->revisionRepository->expects(self::once())->method('saveAll')->with($repository, [$revision]);
        $this->bus->expects(self::once())->method('dispatch')->with(new NewRevisionMessage(456))->willReturn($this->envelope);

        ($this->messageHandler)(new CommitAddedMessage(123, 'hash'));
    }
}

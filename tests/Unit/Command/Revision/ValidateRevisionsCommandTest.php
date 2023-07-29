<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Command\Revision;

use DR\Review\Command\Revision\ValidateRevisionsCommand;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Message\Revision\CommitAddedMessage;
use DR\Review\Message\Revision\CommitRemovedMessage;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\Git\Log\LockableGitLogService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use function DR\PHPUnitExtensions\Mock\consecutive;

/**
 * @coversDefaultClass \DR\Review\Command\Revision\ValidateRevisionsCommand
 * @covers ::__construct
 */
class ValidateRevisionsCommandTest extends AbstractTestCase
{
    private RepositoryRepository&MockObject  $repositoryRepository;
    private RevisionRepository&MockObject    $revisionRepository;
    private LockableGitLogService&MockObject $logService;
    private MessageBusInterface&MockObject   $bus;
    private ValidateRevisionsCommand         $command;
    private Envelope                         $envelope;

    public function setUp(): void
    {
        parent::setUp();
        $this->envelope             = new Envelope(new stdClass(), []);
        $this->repositoryRepository = $this->createMock(RepositoryRepository::class);
        $this->revisionRepository   = $this->createMock(RevisionRepository::class);
        $this->logService           = $this->createMock(LockableGitLogService::class);
        $this->bus                  = $this->createMock(MessageBusInterface::class);
        $this->command              = new ValidateRevisionsCommand(
            $this->repositoryRepository,
            $this->revisionRepository,
            $this->logService,
            $this->bus
        );
    }

    /**
     * @covers ::execute
     * @throws ExceptionInterface
     */
    public function testExecute(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $localHashes  = ['old-hash', 'existing-hash'];
        $remoteHashes = ['new-hash', 'existing-hash'];

        $this->repositoryRepository->expects(self::once())->method('findByValidateRevisions')->willReturn([$repository]);
        $this->repositoryRepository->expects(self::once())->method('save')->with($repository, true);
        $this->revisionRepository->expects(self::once())->method('getCommitHashes')->with($repository)->willReturn($localHashes);
        $this->logService->expects(self::once())->method('getCommitHashes')->with($repository)->willReturn($remoteHashes);
        $this->bus->expects(self::exactly(2))
            ->method('dispatch')
            ->with(...consecutive([new CommitAddedMessage(123, 'new-hash')], [new CommitRemovedMessage(123, 'old-hash')]))
            ->willReturn($this->envelope);

        static::assertSame(Command::SUCCESS, $this->command->run(new ArrayInput([]), new NullOutput()));
    }
}

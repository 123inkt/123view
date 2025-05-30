<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Revision;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Message\Revision\CommitAddedMessage;
use DR\Review\Message\Revision\CommitRemovedMessage;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\Git\Log\LockableGitLogService;
use DR\Review\Service\Revision\RevisionValidationService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Messenger\MessageBusInterface;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(RevisionValidationService::class)]
class RevisionValidationServiceTest extends AbstractTestCase
{
    private RepositoryRepository&MockObject  $repositoryRepository;
    private RevisionRepository&MockObject    $revisionRepository;
    private LockableGitLogService&MockObject $logService;
    private MessageBusInterface&MockObject   $bus;
    private RevisionValidationService        $validationService;

    public function setUp(): void
    {
        parent::setUp();
        $this->repositoryRepository = $this->createMock(RepositoryRepository::class);
        $this->revisionRepository   = $this->createMock(RevisionRepository::class);
        $this->logService           = $this->createMock(LockableGitLogService::class);
        $this->bus                  = $this->createMock(MessageBusInterface::class);
        $this->validationService    = new RevisionValidationService(
            $this->repositoryRepository,
            $this->revisionRepository,
            $this->logService,
            $this->bus
        );
    }

    public function testValidate(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $localHashes  = ['old-hash', 'existing-hash'];
        $remoteHashes = ['new-hash', 'existing-hash'];

        $this->repositoryRepository->expects($this->once())->method('save')->with($repository, true);
        $this->revisionRepository->expects($this->once())->method('getCommitHashes')->with($repository)->willReturn($localHashes);
        $this->logService->expects($this->once())->method('getCommitHashes')->with($repository)->willReturn($remoteHashes);
        $this->bus->expects($this->exactly(2))
            ->method('dispatch')
            ->with(...consecutive([new CommitAddedMessage(123, 'new-hash')], [new CommitRemovedMessage(123, 'old-hash')]))
            ->willReturn($this->envelope);

        $this->validationService->validate($repository);
    }
}

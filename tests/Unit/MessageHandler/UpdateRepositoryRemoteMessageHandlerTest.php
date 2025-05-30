<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Message\Revision\RepositoryUpdatedMessage;
use DR\Review\MessageHandler\UpdateRepositoryRemoteMessageHandler;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Service\Git\Remote\LockableGitRemoteService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(UpdateRepositoryRemoteMessageHandler::class)]
class UpdateRepositoryRemoteMessageHandlerTest extends AbstractTestCase
{
    private RepositoryRepository&MockObject $repositoryRepository;
    private LockableGitRemoteService&MockObject $remoteService;
    private UpdateRepositoryRemoteMessageHandler $messageHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryRepository = $this->createMock(RepositoryRepository::class);
        $this->remoteService        = $this->createMock(LockableGitRemoteService::class);
        $this->messageHandler       = new UpdateRepositoryRemoteMessageHandler($this->repositoryRepository, $this->remoteService);
    }

    /**
     * @throws Throwable
     */
    public function testInvokeUnknownRepository(): void
    {
        $event = new RepositoryUpdatedMessage(123);

        $this->repositoryRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->remoteService->expects(self::never())->method('updateRemoteUrl');

        ($this->messageHandler)($event);
    }

    /**
     * @throws Throwable
     */
    public function testInvoke(): void
    {
        $event      = new RepositoryUpdatedMessage(123);
        $repository = new Repository();

        $this->repositoryRepository->expects($this->once())->method('find')->with(123)->willReturn($repository);
        $this->remoteService->expects($this->once())->method('updateRemoteUrl')->with($repository);

        ($this->messageHandler)($event);
    }
}

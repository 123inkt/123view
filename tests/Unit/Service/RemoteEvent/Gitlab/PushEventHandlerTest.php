<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\RemoteEvent\Gitlab;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Message\Revision\FetchRepositoryRevisionsMessage;
use DR\Review\Model\Webhook\Gitlab\PushEvent;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Service\RemoteEvent\Gitlab\PushEventHandler;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use stdClass;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(PushEventHandler::class)]
class PushEventHandlerTest extends AbstractTestCase
{
    private RepositoryRepository&MockObject $repository;
    private MessageBusInterface&MockObject  $bus;
    private PushEventHandler                $eventHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository   = $this->createMock(RepositoryRepository::class);
        $this->bus          = $this->createMock(MessageBusInterface::class);
        $this->eventHandler = new PushEventHandler($this->repository, $this->bus);
    }

    public function testHandleInvalidEvent(): void
    {
        $this->repository->expects($this->never())->method('findByProperty');
        $this->bus->expects($this->never())->method('dispatch');
        $event = new stdClass();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expecting value to be instance of ' . PushEvent::class);
        $this->eventHandler->handle($event); // @phpstan-ignore-line
    }

    public function testHandleUnknownRepository(): void
    {
        $event            = new PushEvent();
        $event->projectId = 1;

        $this->repository->expects($this->once())->method('findByProperty')->with('gitlab-project-id', '1')->willReturn(null);
        $this->bus->expects($this->never())->method('dispatch');

        $this->eventHandler->handle($event);
    }

    public function testHandleInactiveRepository(): void
    {
        $event            = new PushEvent();
        $event->projectId = 1;
        $repository       = (new Repository())->setActive(false);

        $this->repository->expects($this->once())->method('findByProperty')->with('gitlab-project-id', '1')->willReturn($repository);
        $this->bus->expects($this->never())->method('dispatch');

        $this->eventHandler->handle($event);
    }

    public function testHandleActiveRepository(): void
    {
        $event            = new PushEvent();
        $event->projectId = 1;
        $repository       = (new Repository())->setId(123)->setActive(true);

        $message = new FetchRepositoryRevisionsMessage(123);

        $this->repository->expects($this->once())->method('findByProperty')->with('gitlab-project-id', '1')->willReturn($repository);
        $this->bus->expects($this->once())->method('dispatch')->with($message)->willReturn($this->envelope);

        $this->eventHandler->handle($event);
    }
}

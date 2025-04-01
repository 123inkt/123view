<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Vcs;

use DR\Review\Controller\App\Vcs\FetchRevisionsController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Message\Revision\FetchRepositoryRevisionsMessage;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(FetchRevisionsController::class)]
class FetchRevisionsControllerTest extends AbstractTestCase
{
    private RepositoryRepository&MockObject $repositoryRepository;
    private MessageBusInterface&MockObject  $bus;
    private FetchRevisionsController        $controller;

    public function setUp(): void
    {
        parent::setUp();
        $this->repositoryRepository = $this->createMock(RepositoryRepository::class);
        $this->bus                  = $this->createMock(MessageBusInterface::class);
        $this->controller           = new FetchRevisionsController($this->repositoryRepository, $this->bus);
    }

    public function testInvokeFindById(): void
    {
        $repository = new Repository();
        $repository->setId(123);

        $this->repositoryRepository->expects(self::once())->method('find')->with('123')->willReturn($repository);
        $this->bus->expects(self::once())->method('dispatch')->with(new FetchRepositoryRevisionsMessage(123))->willReturn($this->envelope);

        $response = ($this->controller)('123');
        static::assertEquals(new Response('Accepted'), $response);
    }

    public function testInvokeFindByName(): void
    {
        $repository = new Repository();
        $repository->setId(123);

        $this->repositoryRepository->expects(self::once())->method('findOneBy')->with(['name' => 'name'])->willReturn($repository);
        $this->bus->expects(self::once())->method('dispatch')->with(new FetchRepositoryRevisionsMessage(123))->willReturn($this->envelope);

        $response = ($this->controller)('name');
        static::assertEquals(new Response('Accepted'), $response);
    }

    public function testInvokeUnknownRepository(): void
    {
        $this->repositoryRepository->expects(self::once())->method('find')->with('123')->willReturn(null);
        $this->bus->expects(self::never())->method('dispatch');

        $response = ($this->controller)('123');
        static::assertEquals(new Response('Rejected', Response::HTTP_BAD_REQUEST), $response);
    }
}

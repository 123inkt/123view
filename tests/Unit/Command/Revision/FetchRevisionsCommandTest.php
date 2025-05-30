<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Command\Revision;

use DR\Review\Command\Revision\FetchRevisionsCommand;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Message\Revision\FetchRepositoryRevisionsMessage;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(FetchRevisionsCommand::class)]
class FetchRevisionsCommandTest extends AbstractTestCase
{
    private RepositoryRepository&MockObject $repositoryRepository;
    private MessageBusInterface&MockObject  $bus;
    private FetchRevisionsCommand           $command;

    public function setUp(): void
    {
        parent::setUp();
        $this->repositoryRepository = $this->createMock(RepositoryRepository::class);
        $this->bus                  = $this->createMock(MessageBusInterface::class);
        $this->command              = new FetchRevisionsCommand($this->repositoryRepository, $this->bus);
    }

    public function testCommandInvalidFrequency(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $repository->setName('foobar');

        $this->repositoryRepository->expects($this->once())->method('findByUpdateRevisions')->willReturn([$repository]);
        $this->repositoryRepository->expects($this->once())->method('save')->with($repository, true);
        $this->bus->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(FetchRepositoryRevisionsMessage::class))
            ->willReturn($this->envelope);

        $commandTester = new CommandTester($this->command);

        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
    }
}

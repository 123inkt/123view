<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Command\Revision;

use DR\Review\Command\Revision\ValidateRevisionsCommand;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Message\Revision\ValidateRevisionsMessage;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(ValidateRevisionsCommand::class)]
class ValidateRevisionsCommandTest extends AbstractTestCase
{
    private RepositoryRepository&MockObject $repositoryRepository;
    private MessageBusInterface&MockObject  $bus;
    private ValidateRevisionsCommand        $command;

    public function setUp(): void
    {
        parent::setUp();
        $this->repositoryRepository = $this->createMock(RepositoryRepository::class);
        $this->bus                  = $this->createMock(MessageBusInterface::class);
        $this->command              = new ValidateRevisionsCommand($this->repositoryRepository, $this->bus);
    }

    /**
     * @throws ExceptionInterface
     */
    public function testExecute(): void
    {
        $repository = new Repository();
        $repository->setId(123);

        $this->repositoryRepository->expects($this->once())->method('findByValidateRevisions')->willReturn([$repository]);
        $this->bus->expects($this->once())->method('dispatch')->with(new ValidateRevisionsMessage(123))->willReturn($this->envelope);

        static::assertSame(Command::SUCCESS, $this->command->run(new ArrayInput([]), new NullOutput()));
    }
}

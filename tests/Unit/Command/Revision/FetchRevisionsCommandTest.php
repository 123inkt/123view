<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Command\Revision;

use DR\Review\Command\Revision\FetchRevisionsCommand;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Message\Revision\FetchRepositoryRevisionsMessage;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \DR\Review\Command\Revision\FetchRevisionsCommand
 * @covers ::__construct
 */
class FetchRevisionsCommandTest extends AbstractTestCase
{
    private RepositoryRepository&MockObject $repositoryRepository;
    private MessageBusInterface&MockObject  $bus;
    private FetchRevisionsCommand           $command;
    private Envelope                        $envelope;

    public function setUp(): void
    {
        parent::setUp();
        $this->repositoryRepository = $this->createMock(RepositoryRepository::class);
        $this->bus                  = $this->createMock(MessageBusInterface::class);
        $this->envelope             = new Envelope(new stdClass(), []);
        $this->command              = new FetchRevisionsCommand($this->repositoryRepository, $this->bus);
    }

    /**
     * @covers ::execute
     */
    public function testCommandInvalidFrequency(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $repository->setName('foobar');

        $this->repositoryRepository->expects(self::once())->method('findByUpdateRevisions')->willReturn([$repository]);
        $this->repositoryRepository->expects(self::once())->method('save')->with($repository, true);
        $this->bus->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(FetchRepositoryRevisionsMessage::class))
            ->willReturn($this->envelope);

        $commandTester = new CommandTester($this->command);

        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Command\Repository;

use DR\Review\Command\Repository\RemoveRepositoryCommand;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @coversDefaultClass \DR\Review\Command\Repository\RemoveRepositoryCommand
 * @covers ::__construct
 */
class RemoveRepositoryCommandTest extends AbstractTestCase
{
    private RepositoryRepository&MockObject $repositoryRepository;
    private RemoveRepositoryCommand         $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryRepository = $this->createMock(RepositoryRepository::class);
        $this->command              = new RemoveRepositoryCommand($this->repositoryRepository);
    }

    /**
     * @covers ::configure
     * @covers ::execute
     * @throws Exception
     */
    public function testExecute(): void
    {
        $repository = new Repository();

        $this->repositoryRepository->expects(self::once())->method('findOneBy')->with(['name' => 'name'])->willReturn($repository);
        $this->repositoryRepository->expects(self::once())->method('remove')->with($repository, true);

        $tester = new CommandTester($this->command);
        $result = $tester->execute(['name' => 'name']);
        static::assertSame(Command::SUCCESS, $result);
    }

    /**
     * @covers ::configure
     * @covers ::execute
     * @throws Exception
     */
    public function testExecuteFailure(): void
    {
        $this->repositoryRepository->expects(self::once())->method('findOneBy')->with(['name' => 'name'])->willReturn(null);
        $this->repositoryRepository->expects(self::never())->method('remove');

        $tester = new CommandTester($this->command);
        $result = $tester->execute(['name' => 'name']);
        static::assertSame(Command::FAILURE, $result);
    }
}

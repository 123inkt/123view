<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Command\Repository;

use DR\Review\Command\Repository\ListRepositoriesCommand;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @coversDefaultClass \DR\Review\Command\Repository\ListRepositoriesCommand
 * @covers ::__construct
 */
class ListRepositoriesCommandTest extends AbstractTestCase
{
    private RepositoryRepository&MockObject $repositoryRepository;
    private ListRepositoriesCommand         $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryRepository = $this->createMock(RepositoryRepository::class);
        $this->command              = new ListRepositoriesCommand($this->repositoryRepository);
    }

    /**
     * @covers ::execute
     * @throws Exception
     */
    public function testExecute(): void
    {
        $repository = new Repository();

        $this->repositoryRepository
            ->expects(self::once())
            ->method('findAll')
            ->willReturn([$repository]);

        $tester = new CommandTester($this->command);
        $result = $tester->execute([]);
        static::assertSame(Command::SUCCESS, $result);
    }

    /**
     * @covers ::execute
     * @throws Exception
     */
    public function testExecuteEmptyList(): void
    {
        $this->repositoryRepository
            ->expects(self::once())
            ->method('findAll')
            ->willReturn([]);

        $tester = new CommandTester($this->command);
        $result = $tester->execute([]);
        static::assertSame(Command::SUCCESS, $result);
    }
}

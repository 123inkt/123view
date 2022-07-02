<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Command\Repository;

use DR\GitCommitNotification\Command\Repository\ListRepositoriesCommand;
use DR\GitCommitNotification\Entity\Repository;
use DR\GitCommitNotification\Repository\RepositoryRepository;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Command\Repository\ListRepositoriesCommand
 * @covers ::__construct
 */
class ListRepositoriesCommandTest extends AbstractTestCase
{
    /** @var MockObject&RepositoryRepository */
    private RepositoryRepository    $repositoryRepository;
    private ListRepositoriesCommand $command;

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
        $input  = new ArrayInput([]);
        $output = new NullOutput();
        $repository   = new Repository();

        $this->repositoryRepository
            ->expects(self::once())
            ->method('findAll')
            ->willReturn([$repository]);

        $result = $this->command->run($input, $output);
        static::assertSame(Command::SUCCESS, $result);
    }

    /**
     * @covers ::execute
     * @throws Exception
     */
    public function testExecuteEmptyList(): void
    {
        $input  = new ArrayInput([]);
        $output = new NullOutput();

        $this->repositoryRepository
            ->expects(self::once())
            ->method('findAll')
            ->willReturn([]);

        $result = $this->command->run($input, $output);
        static::assertSame(Command::SUCCESS, $result);
    }
}

<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Command\Repository;

use DR\GitCommitNotification\Command\Repository\RemoveRepositoryCommand;
use DR\GitCommitNotification\Entity\Repository;
use DR\GitCommitNotification\Repository\RepositoryRepository;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Command\Repository\RemoveRepositoryCommand
 * @covers ::__construct
 */
class RemoveRepositoryCommandTest extends AbstractTestCase
{
    /** @var MockObject&RepositoryRepository */
    private RepositoryRepository    $repositoryRepository;
    private RemoveRepositoryCommand $command;

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
        $input      = new ArrayInput(['name' => 'name']);
        $output     = new NullOutput();
        $repository = new Repository();

        $this->repositoryRepository->expects(self::once())->method('findOneBy')->with(['name' => 'name'])->willReturn($repository);
        $this->repositoryRepository->expects(self::once())->method('remove')->with($repository, true);

        $result = $this->command->run($input, $output);
        static::assertSame(Command::SUCCESS, $result);
    }

    /**
     * @covers ::configure
     * @covers ::execute
     * @throws Exception
     */
    public function testExecuteFailure(): void
    {
        $input  = new ArrayInput(['name' => 'name']);
        $output = new NullOutput();

        $this->repositoryRepository->expects(self::once())->method('findOneBy')->with(['name' => 'name'])->willReturn(null);
        $this->repositoryRepository->expects(self::never())->method('remove');

        $result = $this->command->run($input, $output);
        static::assertSame(Command::FAILURE, $result);
    }
}

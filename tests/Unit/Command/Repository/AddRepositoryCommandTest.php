<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Command\Repository;

use DigitalRevolution\SymfonyConsoleValidation\InputValidator;
use DR\GitCommitNotification\Command\Repository\AddRepositoryCommand;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Config\RepositoryProperty;
use DR\GitCommitNotification\Repository\Config\RepositoryRepository;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Validator\Validation;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Command\Repository\AddRepositoryCommand
 * @covers ::__construct
 */
class AddRepositoryCommandTest extends AbstractTestCase
{
    /** @var MockObject&RepositoryRepository */
    private RepositoryRepository $repositoryRepository;
    private AddRepositoryCommand $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryRepository = $this->createMock(RepositoryRepository::class);
        $inputValidator             = new InputValidator(Validation::createValidator());
        $this->command              = new AddRepositoryCommand($this->repositoryRepository, $inputValidator);
    }

    /**
     * @covers ::configure
     * @covers ::execute
     * @throws Exception
     */
    public function testExecuteMissingName(): void
    {
        $tester = new CommandTester($this->command);
        $result = $tester->execute(['repository' => 'foobar']);

        static::assertSame(Command::FAILURE, $result);
        static::assertStringContainsString('Unable to determine the name of the repository.', $tester->getDisplay());
    }

    /**
     * @covers ::configure
     * @covers ::execute
     * @throws Exception
     */
    public function testExecuteDuplicateRepository(): void
    {
        $this->repositoryRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['name' => 'foobar'])
            ->willReturn(new Repository());

        $tester = new CommandTester($this->command);
        $result = $tester->execute(['repository' => 'http://my/foobar']);

        static::assertSame(Command::FAILURE, $result);
        static::assertStringContainsString('A repository with name `foobar` already exists.', $tester->getDisplay());
    }

    /**
     * @covers ::configure
     * @covers ::execute
     * @throws Exception
     */
    public function testExecuteMinimalArguments(): void
    {
        $repository = new Repository();
        $repository->setUrl('http://my/foobar');
        $repository->setName('foobar');

        $this->repositoryRepository->expects(self::once())->method('findOneBy')->with(['name' => 'foobar'])->willReturn(null);
        $this->repositoryRepository->expects(self::once())->method('add')->with($repository);

        $tester = new CommandTester($this->command);
        $tester->execute(['repository' => 'http://my/foobar']);
        $tester->assertCommandIsSuccessful();
    }

    /**
     * @covers ::configure
     * @covers ::execute
     * @throws Exception
     */
    public function testExecuteFullArguments(): void
    {
        $repository = new Repository();
        $repository->setUrl('http://my/foobar');
        $repository->setName('name');
        $repository->addRepositoryProperty(new RepositoryProperty('upsource-project-id', 'upsource'));
        $repository->addRepositoryProperty(new RepositoryProperty('gitlab-project-id', '123'));

        $this->repositoryRepository->expects(self::once())->method('findOneBy')->with(['name' => 'name'])->willReturn(null);
        $this->repositoryRepository->expects(self::once())->method('add')->with($repository);

        $tester = new CommandTester($this->command);
        $tester->execute(
            [
                'repository' => 'http://my/foobar',
                '--name'     => 'name',
                '--upsource' => 'upsource',
                '--gitlab'   => 123
            ]
        );
        $tester->assertCommandIsSuccessful();
    }
}

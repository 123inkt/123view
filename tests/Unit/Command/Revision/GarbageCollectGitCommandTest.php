<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Command\Revision;

use DR\Review\Command\Revision\GarbageCollectGitCommand;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Service\Git\GarbageCollect\LockableGitGarbageCollectService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(GarbageCollectGitCommand::class)]
class GarbageCollectGitCommandTest extends AbstractTestCase
{
    private RepositoryRepository&MockObject             $repositoryRepository;
    private LockableGitGarbageCollectService&MockObject $garbageCollectService;
    private GarbageCollectGitCommand                    $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryRepository  = $this->createMock(RepositoryRepository::class);
        $this->garbageCollectService = $this->createMock(LockableGitGarbageCollectService::class);
        $this->command               = new GarbageCollectGitCommand($this->repositoryRepository, $this->garbageCollectService);
    }

    public function testSuccess(): void
    {
        $repository = new Repository();
        $repository->setName('foobar');

        $this->repositoryRepository->expects($this->once())->method('findBy')->with(['active' => true])->willReturn([$repository]);
        $this->garbageCollectService->expects($this->once())->method('garbageCollect')->with($repository, 'now');

        $commandTester = new CommandTester($this->command);
        static::assertSame(Command::SUCCESS, $commandTester->execute([]));
    }

    public function testFailure(): void
    {
        $repository = new Repository();
        $repository->setName('foobar');

        $this->repositoryRepository->expects($this->once())->method('findBy')->with(['active' => true])->willReturn([$repository]);
        $this->garbageCollectService->expects($this->once())->method('garbageCollect')->willThrowException(new RuntimeException('fail'));

        $commandTester = new CommandTester($this->command);
        static::assertSame(Command::SUCCESS, $commandTester->execute([]));
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Git\GitRepository;
use DR\Review\Service\Git\Clone\GitCloneCommandBuilder;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\GitRepositoryFactory;
use DR\Review\Service\Git\GitRepositoryLocationService;
use DR\Review\Service\Git\GitRepositoryLockManager;
use DR\Review\Service\Git\GitRepositoryService;
use DR\Review\Service\Util\MessageSanitizer;
use DR\Review\Tests\AbstractTestCase;
use League\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

#[CoversClass(GitRepositoryService::class)]
class GitRepositoryServiceTest extends AbstractTestCase
{
    private Filesystem&MockObject                   $filesystem;
    private GitCommandBuilderFactory&MockObject     $commandBuilderFactory;
    private GitRepositoryFactory&MockObject         $repositoryFactory;
    private GitRepositoryLockManager&MockObject     $lockManager;
    private GitRepositoryLocationService&MockObject $locationService;
    private MessageSanitizer                        $messageSanitizer;
    private GitRepositoryService                    $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filesystem            = $this->createMock(Filesystem::class);
        $this->commandBuilderFactory = $this->createMock(GitCommandBuilderFactory::class);
        $this->repositoryFactory     = $this->createMock(GitRepositoryFactory::class);
        $this->lockManager           = $this->createMock(GitRepositoryLockManager::class);
        $this->locationService       = $this->createMock(GitRepositoryLocationService::class);
        $this->messageSanitizer      = static::createStub(MessageSanitizer::class);
        $this->service               = new GitRepositoryService(
            static::createStub(LoggerInterface::class),
            $this->filesystem,
            null,
            $this->locationService,
            $this->commandBuilderFactory,
            $this->repositoryFactory,
            $this->lockManager,
            $this->messageSanitizer,
        );
    }

    /**
     * Already-cloned repository: no lock, no clone, direct factory call.
     *
     * @throws RepositoryException
     */
    public function testGetRepositoryWithCache(): void
    {
        $repository    = new Repository();
        $repository->setId(123);
        $repository->setUrl(Uri::new('https://my.repository.com'));
        $gitRepository = static::createStub(GitRepository::class);

        $this->locationService->expects($this->once())->method('getLocation')->willReturn('/repository/dir/');
        $this->filesystem->expects($this->once())->method('mkdir')->with('/repository');
        $this->filesystem->expects($this->once())->method('exists')->with('/repository/dir/.git')->willReturn(true);
        $this->repositoryFactory->expects($this->once())
            ->method('create')
            ->with($repository, '/repository/dir/')
            ->willReturn($gitRepository);
        $this->lockManager->expects($this->never())->method('lockAcquired');
        $this->commandBuilderFactory->expects($this->never())->method('createClone');

        $result = $this->service->getRepository($repository);
        static::assertSame($gitRepository, $result);
    }

    /**
     * No cache + lock held: clone to temp, rename to final.
     *
     * @throws RepositoryException
     */
    public function testGetRepositoryWithoutCache(): void
    {
        $repository    = new Repository();
        $repository->setId(123);
        $repository->setUrl(Uri::new('https://my.repository.com'));

        $cloneBuilder  = $this->createMock(GitCloneCommandBuilder::class);
        $bootstrapRepo = $this->createMock(GitRepository::class);
        $finalRepo     = static::createStub(GitRepository::class);

        $this->locationService->expects($this->once())->method('getLocation')->willReturn('/repository/dir/');
        $this->filesystem->expects($this->once())->method('mkdir')->with('/repository');
        // First exists() = no .git; second exists() = re-check after lock guard, still no .git
        $this->filesystem->expects($this->exactly(2))->method('exists')->willReturn(false);
        $this->lockManager->expects($this->once())->method('lockAcquired')->willReturn(true);
        $this->filesystem->expects($this->once())->method('remove')->with('/repository/dir.tmp');

        $cloneBuilder->expects($this->once())->method('repository')->willReturnSelf();
        $cloneBuilder->expects($this->once())->method('directory')->with('/repository/dir.tmp')->willReturnSelf();
        $this->commandBuilderFactory->expects($this->once())->method('createClone')->willReturn($cloneBuilder);

        $this->repositoryFactory->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls($bootstrapRepo, $finalRepo);

        $bootstrapRepo->expects($this->once())->method('execute')->with($cloneBuilder, false, null);
        $this->filesystem->expects($this->once())->method('rename')->with('/repository/dir.tmp', '/repository/dir');

        $result = $this->service->getRepository($repository);
        static::assertSame($finalRepo, $result);
    }

    /**
     * No cache + lock held + another process cloned between the two exists() checks: returns repo without cloning.
     *
     * @throws RepositoryException
     */
    public function testGetRepositoryWithoutCacheRaceCondition(): void
    {
        $repository    = new Repository();
        $repository->setId(123);
        $repository->setUrl(Uri::new('https://my.repository.com'));
        $gitRepository = static::createStub(GitRepository::class);

        $this->locationService->expects(static::once())->method('getLocation')->willReturn('/repository/dir/');
        $this->filesystem->expects(static::once())->method('mkdir')->with('/repository');
        // First exists() = no .git; second exists() = .git appeared while we held the lock
        $this->filesystem->expects(static::exactly(2))->method('exists')->willReturn(false, true);
        $this->lockManager->expects(static::once())->method('lockAcquired')->willReturn(true);
        $this->repositoryFactory->expects(static::once())
            ->method('create')
            ->with($repository, '/repository/dir/')
            ->willReturn($gitRepository);
        $this->commandBuilderFactory->expects(static::never())->method('createClone');

        $result = $this->service->getRepository($repository);
        static::assertSame($gitRepository, $result);
    }

    /**
     * No cache, no lock: circuit breaker retries 5× then throws.
     *
     * @throws RepositoryException
     */
    public function testGetRepositoryWithoutCacheAndWithoutLockThrows(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $repository->setUrl(Uri::new('https://my.repository.com'));

        // Circuit breaker retries 5 times before giving up.
        $this->locationService->expects($this->exactly(5))->method('getLocation')->willReturn('/repository/dir/');
        $this->filesystem->expects($this->exactly(5))->method('mkdir');
        $this->filesystem->expects($this->exactly(5))->method('exists')->willReturn(false);
        $this->lockManager->expects($this->exactly(5))->method('lockAcquired')->willReturn(false);
        $this->commandBuilderFactory->expects($this->never())->method('createClone');
        $this->repositoryFactory->expects($this->never())->method('create');

        $this->expectException(RepositoryException::class);
        $this->service->getRepository($repository);
    }

    /**
     * Clone process fails: temp dir removed each attempt, RepositoryException on all 5 retries.
     *
     * @throws RepositoryException
     */
    public function testGetRepositoryCloneFailureRetriesAndThrows(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $repository->setUrl(Uri::new('https://my.repository.com'));

        $cloneBuilder  = $this->createMock(GitCloneCommandBuilder::class);
        $bootstrapRepo = $this->createMock(GitRepository::class);

        $this->locationService->expects($this->exactly(5))->method('getLocation')->willReturn('/repository/dir/');
        $this->filesystem->expects($this->exactly(5))->method('mkdir');
        // Two exists() calls per attempt: initial check + re-check after lock guard
        $this->filesystem->expects($this->exactly(10))->method('exists')->willReturn(false);
        $this->lockManager->expects($this->exactly(5))->method('lockAcquired')->willReturn(true);
        // remove() is called twice per attempt: once before clone, once in the catch block
        $this->filesystem->expects($this->exactly(10))->method('remove');

        $cloneBuilder->expects($this->exactly(5))->method('repository')->willReturnSelf();
        $cloneBuilder->expects($this->exactly(5))->method('directory')->willReturnSelf();
        $this->commandBuilderFactory->expects($this->exactly(5))->method('createClone')->willReturn($cloneBuilder);
        $this->repositoryFactory->expects($this->exactly(5))->method('create')->willReturn($bootstrapRepo);

        $process = static::createStub(Process::class);
        $process->method('getErrorOutput')->willReturn('fatal: repo not found');
        $process->method('getExitCode')->willReturn(128);
        $exception = new ProcessFailedException($process);

        $bootstrapRepo->expects($this->exactly(5))->method('execute')->willThrowException($exception);

        $messageSanitizer = $this->createMock(MessageSanitizer::class);
        $messageSanitizer->expects($this->exactly(5))
            ->method('sanitize')
            ->with('git: clone failed (exit 128): fatal: repo not found', static::anything())
            ->willReturnArgument(0);

        $service = new GitRepositoryService(
            static::createStub(LoggerInterface::class),
            $this->filesystem,
            null,
            $this->locationService,
            $this->commandBuilderFactory,
            $this->repositoryFactory,
            $this->lockManager,
            $messageSanitizer,
        );

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('fatal: repo not found');
        $service->getRepository($repository);
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git;

use CzProject\GitPhp\Git;
use CzProject\GitPhp\GitException;
use CzProject\GitPhp\GitRepository;
use CzProject\GitPhp\RunnerResult;
use DR\Review\Entity\Repository\Credential\BasicAuthCredential;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Repository\RepositoryCredential;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\GitRepositoryLocationService;
use DR\Review\Service\Git\GitRepositoryService;
use DR\Review\Tests\AbstractTestCase;
use InvalidArgumentException;
use League\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

#[CoversClass(GitRepositoryService::class)]
class GitRepositoryServiceTest extends AbstractTestCase
{
    private Filesystem&MockObject                   $filesystem;
    private Git&MockObject                          $git;
    private GitRepositoryLocationService&MockObject $locationService;
    private GitRepositoryService                    $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->git             = $this->createMock(Git::class);
        $this->filesystem      = $this->createMock(Filesystem::class);
        $this->locationService = $this->createMock(GitRepositoryLocationService::class);
        $this->service         = new GitRepositoryService(
            static::createStub(LoggerInterface::class),
            $this->git,
            $this->filesystem,
            null,
            $this->locationService
        );
    }

    /**
     * @throws RepositoryException
     */
    public function testGetRepositoryWithoutCache(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $repository->setUrl(Uri::new('https://my.repository.com'));
        $gitRepository = static::createStub(GitRepository::class);

        // setup mocks
        $this->locationService->expects($this->once())->method('getLocation')->with($repository)->willReturn('/repository/dir');
        $this->filesystem->expects($this->once())->method('mkdir')->with('/repository');
        $this->filesystem->expects($this->once())->method('exists')->willReturn(false);
        $this->git->expects($this->once())->method('cloneRepository')->with('https://my.repository.com')->willReturn($gitRepository);

        $this->service->getRepository($repository);
    }

    /**
     * @throws RepositoryException
     */
    public function testGetRepositoryWithCredentials(): void
    {
        $credential = new RepositoryCredential();
        $credential->setCredentials(new BasicAuthCredential('user', 'pass'));

        $repository = new Repository();
        $repository->setCredential($credential);
        $repository->setId(123);
        $repository->setUrl(Uri::new('https://my.repository.com'));
        $gitRepository = static::createStub(GitRepository::class);

        // setup mocks
        $this->locationService->expects($this->once())->method('getLocation')->with($repository)->willReturn('/repository/dir');
        $this->filesystem->expects($this->once())->method('mkdir')->with('/repository');
        $this->filesystem->expects($this->once())->method('exists')->willReturn(false);
        $this->git->expects($this->once())->method('cloneRepository')->with('https://user:pass@my.repository.com')->willReturn($gitRepository);

        $this->service->getRepository($repository);
    }

    /**
     * @throws RepositoryException
     */
    public function testGetRepositoryWithCache(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $repository->setUrl(Uri::new('https://my.repository.com'));

        // setup mocks
        $this->locationService->expects($this->once())->method('getLocation')->with($repository)->willReturn('/repository/dir');
        $this->filesystem->expects($this->once())->method('mkdir')->with('/repository');
        $this->filesystem->expects($this->once())->method('exists')->willReturn(true);
        $this->git->expects($this->never())->method('cloneRepository');

        $this->service->getRepository($repository);
    }

    /**
     * @throws RepositoryException
     */
    public function testGetRepositoryWithGitException(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $repository->setUrl(Uri::new('https://my.repository.com'));
        $runnerResult = new RunnerResult('git', 1, ['output'], ['failure']);

        // setup mocks
        $this->locationService->expects($this->exactly(5))->method('getLocation')->with($repository)->willReturn('/repository/dir');
        $this->filesystem->expects($this->exactly(5))->method('mkdir')->with('/repository');
        $this->filesystem->expects($this->exactly(5))->method('exists')->willReturn(false);
        $this->git->expects($this->exactly(5))->method('cloneRepository')->willThrowException(new GitException('exception', 5, null, $runnerResult));

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('failure');
        $this->service->getRepository($repository);
    }

    /**
     * @throws RepositoryException
     */
    public function testGetRepositoryWithException(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $repository->setUrl(Uri::new('https://my.repository.com'));

        // setup mocks
        $this->locationService->expects($this->exactly(5))->method('getLocation')->with($repository)->willReturn('/repository/dir');
        $this->filesystem->expects($this->exactly(5))->method('mkdir')->with('/repository');
        $this->filesystem->expects($this->exactly(5))->method('exists')->willReturn(false);
        $this->git->expects($this->exactly(5))->method('cloneRepository')->willThrowException(new InvalidArgumentException('foobar'));

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('foobar');
        $this->service->getRepository($repository);
    }
}

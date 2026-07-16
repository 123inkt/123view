<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git;

use CzProject\GitPhp\Git;
use CzProject\GitPhp\GitException;
use CzProject\GitPhp\GitRepository;
use CzProject\GitPhp\RunnerResult;
use DR\Review\Doctrine\Type\AuthenticationType;
use DR\Review\Entity\Repository\Credential\BasicAuthCredential;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Repository\RepositoryCredential;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\GitRepositoryLocationService;
use DR\Review\Service\Git\GitRepositoryService;
use DR\Review\Service\Git\Ssh\GitSshSetupService;
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
    private GitSshSetupService&MockObject           $sshSetupService;
    private GitRepositoryService                    $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->git             = $this->createMock(Git::class);
        $this->filesystem      = $this->createMock(Filesystem::class);
        $this->locationService = $this->createMock(GitRepositoryLocationService::class);
        $this->sshSetupService = $this->createMock(GitSshSetupService::class);
        $this->service         = new GitRepositoryService(
            static::createStub(LoggerInterface::class),
            $this->git,
            $this->filesystem,
            null,
            $this->locationService,
            $this->sshSetupService,
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

        $this->locationService->expects($this->once())->method('getLocation')->with($repository)->willReturn('/repository/dir');
        $this->filesystem->expects($this->once())->method('mkdir')->with('/repository');
        $this->filesystem->expects($this->once())->method('exists')->willReturn(false);
        $this->git->expects($this->once())->method('cloneRepository')->with('https://my.repository.com')->willReturn($gitRepository);
        $this->sshSetupService->expects($this->never())->method('withSshAuth');

        $this->service->getRepository($repository);
    }

    /**
     * Basic Auth regression: clone with BasicAuth embeds credentials in the URL; withSshAuth is never called.
     *
     * @throws RepositoryException
     */
    public function testGetRepositoryWithBasicAuthCredential(): void
    {
        $credential = new RepositoryCredential();
        $credential->setCredentials(new BasicAuthCredential('user', 'pass'));

        $repository = new Repository();
        $repository->setCredential($credential);
        $repository->setId(123);
        $repository->setUrl(Uri::new('https://my.repository.com'));
        $gitRepository = static::createStub(GitRepository::class);

        $this->locationService->expects($this->once())->method('getLocation')->with($repository)->willReturn('/repository/dir');
        $this->filesystem->expects($this->once())->method('mkdir')->with('/repository');
        $this->filesystem->expects($this->once())->method('exists')->willReturn(false);
        $this->git->expects($this->once())->method('cloneRepository')->with('https://user:pass@my.repository.com')->willReturn($gitRepository);
        $this->sshSetupService->expects($this->never())->method('withSshAuth');

        $this->service->getRepository($repository);
    }

    /**
     * SSH path: clone with SSH credential delegates to withSshAuth; URL contains no embedded credentials.
     *
     * @throws RepositoryException
     */
    public function testGetRepositoryWithSshCredential(): void
    {
        $credential    = (new RepositoryCredential())->setAuthType(AuthenticationType::SSH_KEY)->setValue('v1:encrypted_key');
        $gitRepository = static::createStub(GitRepository::class);

        $repository = new Repository();
        $repository->setId(123);
        $repository->setUrl(Uri::new('ssh://git@github.com/org/repo.git'));
        $repository->setCredential($credential);

        $this->locationService->expects($this->once())->method('getLocation')->willReturn('/repository/dir');
        $this->filesystem->expects($this->once())->method('mkdir');
        $this->filesystem->expects($this->once())->method('exists')->willReturn(false);

        // withSshAuth must be called with the credential and must invoke the callback
        $this->sshSetupService->expects($this->once())->method('withSshAuth')
            ->with($credential)
            ->willReturnCallback(static fn($credential, callable $callback) => $callback(['GIT_SSH_COMMAND' => 'ssh ...']));

        // Clone URL must be the bare SSH URL — no embedded credentials
        $this->git->expects($this->once())->method('cloneRepository')
            ->with('ssh://git@github.com/org/repo.git')
            ->willReturn($gitRepository);

        $this->service->getRepository($repository);
    }

    /**
     * SSH path: withSshAuth propagates exceptions from the clone operation to getRepository's caller.
     *
     * @throws RepositoryException
     */
    public function testGetRepositoryWithSshCredentialPropagatesCloneFailure(): void
    {
        $credential = (new RepositoryCredential())->setAuthType(AuthenticationType::SSH_KEY)->setValue('v1:encrypted_key');

        $repository = new Repository();
        $repository->setId(123);
        $repository->setUrl(Uri::new('ssh://git@github.com/org/repo.git'));
        $repository->setCredential($credential);

        $this->locationService->expects($this->exactly(5))->method('getLocation')->willReturn('/repository/dir');
        $this->filesystem->expects($this->exactly(5))->method('mkdir');
        $this->filesystem->expects($this->exactly(5))->method('exists')->willReturn(false);

        $this->sshSetupService->expects($this->exactly(5))->method('withSshAuth')
            ->willReturnCallback(static fn($credential, callable $callback) => $callback(['GIT_SSH_COMMAND' => 'ssh ...']));

        $this->git->expects($this->exactly(5))->method('cloneRepository')
            ->willThrowException(new GitException('clone failed'));

        $this->expectException(RepositoryException::class);
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

        $this->locationService->expects($this->once())->method('getLocation')->with($repository)->willReturn('/repository/dir');
        $this->filesystem->expects($this->once())->method('mkdir')->with('/repository');
        $this->filesystem->expects($this->once())->method('exists')->willReturn(true);
        $this->git->expects($this->never())->method('cloneRepository');
        $this->sshSetupService->expects($this->never())->method('withSshAuth');

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

        $this->locationService->expects($this->exactly(5))->method('getLocation')->with($repository)->willReturn('/repository/dir');
        $this->filesystem->expects($this->exactly(5))->method('mkdir')->with('/repository');
        $this->filesystem->expects($this->exactly(5))->method('exists')->willReturn(false);
        $this->git->expects($this->exactly(5))->method('cloneRepository')->willThrowException(new GitException('exception', 5, null, $runnerResult));
        $this->sshSetupService->expects($this->never())->method('withSshAuth');

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

        $this->locationService->expects($this->exactly(5))->method('getLocation')->with($repository)->willReturn('/repository/dir');
        $this->filesystem->expects($this->exactly(5))->method('mkdir')->with('/repository');
        $this->filesystem->expects($this->exactly(5))->method('exists')->willReturn(false);
        $this->git->expects($this->exactly(5))->method('cloneRepository')->willThrowException(new InvalidArgumentException('foobar'));
        $this->sshSetupService->expects($this->never())->method('withSshAuth');

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('foobar');
        $this->service->getRepository($repository);
    }
}

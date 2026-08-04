<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Fetch;

use DR\Review\Doctrine\Type\AuthenticationType;
use DR\Review\Entity\Git\Fetch\BranchUpdate;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Repository\RepositoryCredential;
use DR\Review\Model\Git\GitRepository;
use DR\Review\Service\Git\Fetch\GitFetchCommandBuilder;
use DR\Review\Service\Git\Fetch\GitFetchService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\GitRepositoryService;
use DR\Review\Service\Git\Ssh\GitSshSetupService;
use DR\Review\Service\Parser\Fetch\GitFetchParser;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use League\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(GitFetchService::class)]
class GitFetchServiceTest extends AbstractTestCase
{
    private GitCommandBuilderFactory&MockObject $commandBuilderFactory;
    private GitFetchParser&MockObject           $fetchParser;
    private GitRepositoryService&MockObject     $repositoryService;
    private GitSshSetupService&MockObject       $sshSetupService;
    private GitFetchService                     $fetchService;

    public function setUp(): void
    {
        parent::setUp();
        $this->commandBuilderFactory = $this->createMock(GitCommandBuilderFactory::class);
        $this->fetchParser           = $this->createMock(GitFetchParser::class);
        $this->repositoryService     = $this->createMock(GitRepositoryService::class);
        $this->sshSetupService       = $this->createMock(GitSshSetupService::class);
        $this->fetchService          = new GitFetchService(
            $this->commandBuilderFactory,
            $this->fetchParser,
            $this->repositoryService,
            $this->sshSetupService,
        );
    }

    /**
     * Basic Auth regression: fetch with no credential never calls withSshAuth.
     *
     * @throws Exception
     */
    public function testFetchWithNoCredential(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $repository->setUrl(Uri::new('https://www.example.com'));
        $change = new BranchUpdate('from', 'to', 'oldBranch', 'newBranch');

        $gitRepository = $this->createMock(GitRepository::class);
        $fetchBuilder  = $this->buildFetchBuilder();

        $this->repositoryService->expects($this->once())->method('getRepository')->with($repository)->willReturn($gitRepository);
        $gitRepository->expects($this->once())->method('execute')->with($fetchBuilder, true)->willReturn('output');
        $this->fetchParser->expects($this->once())->method('parse')->with('output')->willReturn([$change]);
        $this->sshSetupService->expects($this->never())->method('withSshAuth');

        static::assertSame([$change], $this->fetchService->fetch($repository));
    }

    /**
     * Basic Auth regression: fetch with a Basic Auth credential never calls withSshAuth.
     *
     * @throws Exception
     */
    public function testFetchWithBasicAuthCredential(): void
    {
        $credential = (new RepositoryCredential())->setAuthType(AuthenticationType::BASIC_AUTH)->setValue('dXNlcjpwYXNz');

        $repository = new Repository();
        $repository->setId(123);
        $repository->setUrl(Uri::new('https://www.example.com'));
        $repository->setCredential($credential);
        $change = new BranchUpdate('from', 'to', 'oldBranch', 'newBranch');

        $gitRepository = $this->createMock(GitRepository::class);
        $fetchBuilder  = $this->buildFetchBuilder();

        $this->repositoryService->expects($this->once())->method('getRepository')->willReturn($gitRepository);
        $gitRepository->expects($this->once())->method('execute')->with($fetchBuilder, true)->willReturn('output');
        $this->fetchParser->expects($this->once())->method('parse')->with('output')->willReturn([$change]);
        $this->sshSetupService->expects($this->never())->method('withSshAuth');

        static::assertSame([$change], $this->fetchService->fetch($repository));
    }

    /**
     * SSH path: fetch with SSH credential delegates to withSshAuth, which passes env to execute.
     *
     * @throws Exception
     */
    public function testFetchWithSshCredential(): void
    {
        $credential = (new RepositoryCredential())->setAuthType(AuthenticationType::SSH_KEY)->setValue('v1:encrypted');

        $repository = new Repository();
        $repository->setId(123);
        $repository->setUrl(Uri::new('ssh://git@github.com/org/repo.git'));
        $repository->setCredential($credential);
        $change = new BranchUpdate('from', 'to', 'oldBranch', 'newBranch');

        $sshEnv        = ['GIT_SSH_COMMAND' => 'ssh -i /tmp/key -o BatchMode=yes'];
        $gitRepository = $this->createMock(GitRepository::class);
        $fetchBuilder  = $this->buildFetchBuilder();

        $this->repositoryService->expects($this->once())->method('getRepository')->willReturn($gitRepository);

        // withSshAuth invokes the callback with the SSH env, simulating the real service
        $this->sshSetupService->expects($this->once())->method('withSshAuth')
            ->with($credential)
            ->willReturnCallback(static fn($credential, callable $callback) => $callback($sshEnv));

        $gitRepository->expects($this->once())->method('execute')->with($fetchBuilder, true, $sshEnv)->willReturn('output');
        $this->fetchParser->expects($this->once())->method('parse')->with('output')->willReturn([$change]);

        static::assertSame([$change], $this->fetchService->fetch($repository));
    }

    /**
     * SSH path: an exception from the callback propagates to the caller (teardown is withSshAuth's responsibility).
     *
     * @throws Exception
     */
    public function testFetchWithSshCredentialPropagatesCallbackException(): void
    {
        $credential = (new RepositoryCredential())->setAuthType(AuthenticationType::SSH_KEY)->setValue('v1:encrypted');

        $repository = new Repository();
        $repository->setId(123);
        $repository->setUrl(Uri::new('ssh://git@github.com/org/repo.git'));
        $repository->setCredential($credential);

        $gitRepository = $this->createMock(GitRepository::class);
        $this->buildFetchBuilder();

        $this->repositoryService->expects($this->once())->method('getRepository')->willReturn($gitRepository);

        $this->sshSetupService->expects($this->once())->method('withSshAuth')
            ->willReturnCallback(static fn($credential, callable $callback) => $callback(['GIT_SSH_COMMAND' => 'ssh ...']));

        $gitRepository->expects($this->once())->method('execute')->willThrowException(new \RuntimeException('git error'));
        $this->fetchParser->expects($this->never())->method('parse');

        $this->expectException(\RuntimeException::class);
        $this->fetchService->fetch($repository);
    }

    private function buildFetchBuilder(): GitFetchCommandBuilder&MockObject
    {
        $fetchBuilder = $this->createMock(GitFetchCommandBuilder::class);
        $fetchBuilder->expects($this->once())->method('all')->willReturnSelf();
        $fetchBuilder->expects($this->once())->method('verbose')->willReturnSelf();
        $fetchBuilder->expects($this->once())->method('noTags')->willReturnSelf();
        $fetchBuilder->expects($this->once())->method('prune')->willReturnSelf();
        $this->commandBuilderFactory->expects($this->once())->method('createFetch')->willReturn($fetchBuilder);

        return $fetchBuilder;
    }
}

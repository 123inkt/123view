<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\CherryPick;

use DR\Review\Entity\Git\CherryPick\CherryPickResult;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Exception\RepositoryException;
use DR\Review\Git\GitRepository;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\CherryPick\GitCherryPickCommandBuilder;
use DR\Review\Service\Git\CherryPick\GitCherryPickParser;
use DR\Review\Service\Git\CherryPick\GitCherryPickService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Tests\AbstractTestCase;
use League\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

#[CoversClass(GitCherryPickService::class)]
class GitCherryPickServiceTest extends AbstractTestCase
{
    private CacheableGitRepositoryService&MockObject $repositoryService;
    private GitCommandBuilderFactory&MockObject      $builderFactory;
    private GitCherryPickParser&MockObject           $cherryPickParser;
    private GitCherryPickService                     $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->repositoryService = $this->createMock(CacheableGitRepositoryService::class);
        $this->builderFactory    = $this->createMock(GitCommandBuilderFactory::class);
        $this->cherryPickParser  = $this->createMock(GitCherryPickParser::class);
        $this->service           = new GitCherryPickService($this->repositoryService, $this->builderFactory, $this->cherryPickParser);
    }

    public function testCherryPickRevisions(): void
    {
        $hash       = '123acbedf';
        $repository = new Repository();
        $repository->setUrl(Uri::createFromString('https://url/'));
        $revision = new Revision();
        $revision->setRepository($repository);
        $revision->setCommitHash($hash);

        $builder = $this->createMock(GitCherryPickCommandBuilder::class);
        $builder->expects(self::once())->method('strategy')->with('ort')->willReturnSelf();
        $builder->expects(self::once())->method('conflictResolution')->with('theirs')->willReturnSelf();
        $builder->expects(self::once())->method('noCommit')->willReturnSelf();
        $builder->expects(self::once())->method('hashes')->with([$hash])->willReturnSelf();
        $this->builderFactory->expects(self::once())->method('createCheryPick')->willReturn($builder);

        $git = $this->createMock(GitRepository::class);
        $git->expects(self::once())->method('execute')->with($builder)->willReturn('output');
        $this->repositoryService->expects(self::once())->method('getRepository')->with($repository)->willReturn($git);

        static::assertTrue($this->service->tryCherryPickRevisions([$revision]));
    }

    /**
     * @throws RepositoryException
     */
    public function testCherryPickRevisionsProcessFailure(): void
    {
        $hash       = '123acbedf';
        $repository = new Repository();
        $repository->setUrl(Uri::createFromString('https://url/'));
        $revision = new Revision();
        $revision->setRepository($repository);
        $revision->setCommitHash($hash);
        $result = new CherryPickResult(false);

        $builder = $this->createMock(GitCherryPickCommandBuilder::class);
        $builder->expects(self::once())->method('strategy')->willReturnSelf();
        $builder->expects(self::once())->method('conflictResolution')->willReturnSelf();
        $builder->expects(self::once())->method('hashes')->willReturnSelf();
        $this->builderFactory->expects(self::once())->method('createCheryPick')->willReturn($builder);

        $process = $this->createMock(Process::class);
        $process->method('getOutput')->willReturn('output');
        $process->method('getErrorOutput')->willReturn('errors');
        $exception = new ProcessFailedException($process);

        $this->repositoryService->expects(self::once())->method('getRepository')->with($repository)->willThrowException($exception);
        $this->cherryPickParser->expects(self::once())->method('parse')->willReturn($result);

        static::assertSame($result, $this->service->cherryPickRevisions([$revision], true));
    }

    public function testCherryPickRevisionsShouldCaptureFailure(): void
    {
        $hash       = '123acbedf';
        $repository = new Repository();
        $repository->setUrl(Uri::createFromString('https://url/'));
        $revision = new Revision();
        $revision->setRepository($repository);
        $revision->setCommitHash($hash);

        $this->builderFactory->expects(self::once())->method('createCheryPick')->willThrowException(new RepositoryException());

        static::assertFalse($this->service->tryCherryPickRevisions([$revision]));
    }

    /**
     * @throws RepositoryException
     */
    public function testCherryAbort(): void
    {
        $repository = new Repository();
        $repository->setUrl(Uri::createFromString('https://url/'));

        $builder = $this->createMock(GitCherryPickCommandBuilder::class);
        $builder->expects(self::once())->method('abort')->willReturnSelf();
        $this->builderFactory->expects(self::once())->method('createCheryPick')->willReturn($builder);

        $git = $this->createMock(GitRepository::class);
        $git->expects(self::once())->method('execute')->with($builder)->willReturn('output');
        $this->repositoryService->expects(self::once())->method('getRepository')->with($repository)->willReturn($git);

        $this->service->cherryPickAbort($repository);
    }

    /**
     * @throws RepositoryException
     */
    public function testCherryContinue(): void
    {
        $repository = new Repository();
        $repository->setUrl(Uri::createFromString('https://url/'));

        $builder = $this->createMock(GitCherryPickCommandBuilder::class);
        $builder->expects(self::once())->method('continue')->willReturnSelf();
        $this->builderFactory->expects(self::once())->method('createCheryPick')->willReturn($builder);

        $git = $this->createMock(GitRepository::class);
        $git->expects(self::once())->method('execute')->with($builder)->willReturn('output');
        $this->repositoryService->expects(self::once())->method('getRepository')->with($repository)->willReturn($git);

        static::assertTrue($this->service->cherryPickContinue($repository)->completed);
    }

    /**
     * @throws RepositoryException
     */
    public function testCherryContinueFailure(): void
    {
        $repository = new Repository();
        $repository->setUrl(Uri::createFromString('https://url/'));

        $exception = new ProcessFailedException($this->createMock(Process::class));
        $result    = new CherryPickResult(false);

        $builder = $this->createMock(GitCherryPickCommandBuilder::class);
        $builder->expects(self::once())->method('continue')->willReturnSelf();
        $this->builderFactory->expects(self::once())->method('createCheryPick')->willReturn($builder);

        $this->repositoryService->expects(self::once())->method('getRepository')->with($repository)->willThrowException($exception);
        $this->cherryPickParser->expects(self::once())->method('parse')->willReturn($result);

        static::assertSame($result, $this->service->cherryPickContinue($repository));
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Grep;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Git\GitRepository;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\GitRepositoryService;
use DR\Review\Service\Git\Grep\GitGrepCommandBuilder;
use DR\Review\Service\Git\Grep\GitGrepService;
use DR\Review\Tests\AbstractTestCase;
use League\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

#[CoversClass(GitGrepService::class)]
class GitGrepServiceTest extends AbstractTestCase
{
    private GitCommandBuilderFactory&MockObject $builderFactory;
    private GitRepositoryService&MockObject     $repositoryService;
    private GitGrepService                      $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builderFactory    = $this->createMock(GitCommandBuilderFactory::class);
        $this->repositoryService = $this->createMock(GitRepositoryService::class);
        $this->service           = new GitGrepService($this->builderFactory, $this->repositoryService);
    }

    public function testGrep(): void
    {
        $repository = new Repository();
        $repository->setUrl(Uri::new('https://url/'));
        
        $revision = new Revision();
        $revision->setRepository($repository);
        $revision->setCommitHash('abc123');

        $builder = $this->createMock(GitGrepCommandBuilder::class);
        $builder->expects($this->once())->method('pattern')->with('test-pattern')->willReturnSelf();
        $builder->expects($this->once())->method('hash')->with('abc123')->willReturnSelf();
        $builder->expects($this->once())->method('fullName')->willReturnSelf();
        $builder->expects($this->once())->method('noColor')->willReturnSelf();
        $builder->expects($this->once())->method('lineNumber')->willReturnSelf();
        $builder->expects($this->once())->method('context')->with(3)->willReturnSelf();

        $this->builderFactory->expects($this->once())->method('createGrep')->willReturn($builder);

        $git = $this->createMock(GitRepository::class);
        $git->expects($this->once())->method('execute')->with($builder)->willReturn('grep output');
        $this->repositoryService->expects($this->once())->method('getRepository')->with($repository)->willReturn($git);

        $result = $this->service->grep($revision, 'test-pattern', 3);
        static::assertSame('grep output', $result);
    }

    public function testGrepWithoutContext(): void
    {
        $repository = new Repository();
        $repository->setUrl(Uri::new('https://url/'));
        
        $revision = new Revision();
        $revision->setRepository($repository);
        $revision->setCommitHash('abc123');

        $builder = $this->createMock(GitGrepCommandBuilder::class);
        $builder->expects($this->once())->method('pattern')->with('test-pattern')->willReturnSelf();
        $builder->expects($this->once())->method('hash')->with('abc123')->willReturnSelf();
        $builder->expects($this->once())->method('fullName')->willReturnSelf();
        $builder->expects($this->once())->method('noColor')->willReturnSelf();
        $builder->expects($this->once())->method('lineNumber')->willReturnSelf();
        $builder->expects($this->never())->method('context');

        $this->builderFactory->expects($this->once())->method('createGrep')->willReturn($builder);

        $git = $this->createMock(GitRepository::class);
        $git->expects($this->once())->method('execute')->with($builder)->willReturn('grep output');
        $this->repositoryService->expects($this->once())->method('getRepository')->with($repository)->willReturn($git);

        $result = $this->service->grep($revision, 'test-pattern', null);
        static::assertSame('grep output', $result);
    }

    public function testGrepReturnsNullOnEmptyOutput(): void
    {
        $repository = new Repository();
        $repository->setUrl(Uri::new('https://url/'));
        
        $revision = new Revision();
        $revision->setRepository($repository);
        $revision->setCommitHash('abc123');

        $builder = $this->createMock(GitGrepCommandBuilder::class);
        $builder->expects($this->once())->method('pattern')->with('no-match')->willReturnSelf();
        $builder->expects($this->once())->method('hash')->with('abc123')->willReturnSelf();
        $builder->expects($this->once())->method('fullName')->willReturnSelf();
        $builder->expects($this->once())->method('noColor')->willReturnSelf();
        $builder->expects($this->once())->method('lineNumber')->willReturnSelf();

        $this->builderFactory->expects($this->once())->method('createGrep')->willReturn($builder);

        $process = static::createStub(Process::class);
        $process->method('getErrorOutput')->willReturn('');
        $process->method('getOutput')->willReturn('');
        $process->method('isSuccessful')->willReturn(false);

        $exception = new ProcessFailedException($process);

        $git = $this->createMock(GitRepository::class);
        $git->expects($this->once())->method('execute')->with($builder)->willThrowException($exception);
        $this->repositoryService->expects($this->once())->method('getRepository')->with($repository)->willReturn($git);

        $result = $this->service->grep($revision, 'no-match');
        static::assertNull($result);
    }

    public function testGrepRethrowsException(): void
    {
        $repository = new Repository();
        $repository->setUrl(Uri::new('https://url/'));
        
        $revision = new Revision();
        $revision->setRepository($repository);
        $revision->setCommitHash('abc123');

        $builder = $this->createMock(GitGrepCommandBuilder::class);
        $builder->expects($this->once())->method('pattern')->with('error-pattern')->willReturnSelf();
        $builder->expects($this->once())->method('hash')->with('abc123')->willReturnSelf();
        $builder->expects($this->once())->method('fullName')->willReturnSelf();
        $builder->expects($this->once())->method('noColor')->willReturnSelf();
        $builder->expects($this->once())->method('lineNumber')->willReturnSelf();

        $this->builderFactory->expects($this->once())->method('createGrep')->willReturn($builder);

        $process = static::createStub(Process::class);
        $process->method('getErrorOutput')->willReturn('fatal: some error');
        $process->method('getOutput')->willReturn('');
        $process->method('isSuccessful')->willReturn(false);

        $exception = new ProcessFailedException($process);

        $git = $this->createMock(GitRepository::class);
        $git->expects($this->once())->method('execute')->with($builder)->willThrowException($exception);
        $this->repositoryService->expects($this->once())->method('getRepository')->with($repository)->willReturn($git);

        $this->expectException(ProcessFailedException::class);
        $this->service->grep($revision, 'error-pattern');
    }
}

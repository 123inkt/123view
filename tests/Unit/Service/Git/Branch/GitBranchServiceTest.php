<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Branch;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Git\GitRepository;
use DR\Review\Service\Git\Branch\GitBranchCommandBuilder;
use DR\Review\Service\Git\Branch\GitBranchService;
use DR\Review\Service\Git\Branch\GitRemoteBranchParser;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Tests\AbstractTestCase;
use League\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(GitBranchService::class)]
class GitBranchServiceTest extends AbstractTestCase
{
    private CacheableGitRepositoryService&MockObject $repositoryService;
    private GitCommandBuilderFactory&MockObject      $builderFactory;
    private GitRemoteBranchParser&MockObject         $branchParser;
    private GitBranchService                         $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->repositoryService = $this->createMock(CacheableGitRepositoryService::class);
        $this->builderFactory    = $this->createMock(GitCommandBuilderFactory::class);
        $this->branchParser      = $this->createMock(GitRemoteBranchParser::class);
        $this->service           = new GitBranchService($this->repositoryService, $this->builderFactory, $this->branchParser);
    }

    /**
     * @throws RepositoryException
     */
    public function testGetRemoteBranches(): void
    {
        $repository = new Repository();

        $builder = $this->createMock(GitBranchCommandBuilder::class);
        $builder->expects($this->once())->method('remote')->willReturnSelf();
        $builder->expects($this->never())->method('merged');
        $this->builderFactory->expects($this->once())->method('createBranch')->willReturn($builder);

        $git = $this->createMock(GitRepository::class);
        $git->expects($this->once())->method('execute')->with($builder)->willReturn('output');
        $this->repositoryService->expects($this->once())->method('getRepository')->with($repository)->willReturn($git);

        $this->branchParser->expects($this->once())->method('parse')->with('output')->willReturn(['branch']);

        static::assertSame(['branch'], $this->service->getRemoteBranches($repository));
    }

    /**
     * @throws RepositoryException
     */
    public function testGetRemoteBranchesMergedOnly(): void
    {
        $repository = new Repository();

        $builder = $this->createMock(GitBranchCommandBuilder::class);
        $builder->expects($this->once())->method('remote')->willReturnSelf();
        $builder->expects($this->once())->method('merged')->willReturnSelf();
        $this->builderFactory->expects($this->once())->method('createBranch')->willReturn($builder);

        $git = $this->createMock(GitRepository::class);
        $git->expects($this->once())->method('execute')->with($builder)->willReturn('output');
        $this->repositoryService->expects($this->once())->method('getRepository')->with($repository)->willReturn($git);

        $this->branchParser->expects($this->once())->method('parse')->with('output')->willReturn(['branch']);

        static::assertSame(['branch'], $this->service->getRemoteBranches($repository, true));
    }

    /**
     * @throws RepositoryException
     */
    public function testDeleteBranch(): void
    {
        $repository = new Repository();
        $repository->setUrl(Uri::new('https://url/'));
        $path = '/foo/bar/';

        $builder = $this->createMock(GitBranchCommandBuilder::class);
        $builder->expects($this->once())->method('delete')->with($path)->willReturnSelf();
        $this->builderFactory->expects($this->once())->method('createBranch')->willReturn($builder);

        $git = $this->createMock(GitRepository::class);
        $git->expects($this->once())->method('execute')->with($builder)->willReturn('output');
        $this->repositoryService->expects($this->once())->method('getRepository')->with($repository)->willReturn($git);

        $this->service->deleteBranch($repository, $path);
    }

    public function testTryDeleteBranch(): void
    {
        $repository = new Repository();
        $repository->setUrl(Uri::new('https://url/'));
        $path = '/foo/bar/';

        $builder = $this->createMock(GitBranchCommandBuilder::class);
        $builder->expects($this->once())->method('delete')->with($path)->willReturnSelf();
        $this->builderFactory->expects($this->once())->method('createBranch')->willReturn($builder);

        $git = $this->createMock(GitRepository::class);
        $git->expects($this->once())->method('execute')->with($builder)->willReturn('output');
        $this->repositoryService->expects($this->once())->method('getRepository')->with($repository)->willReturn($git);

        static::assertTrue($this->service->tryDeleteBranch($repository, $path));
    }

    public function testTryDeleteBranchCaptureException(): void
    {
        $repository = new Repository();
        $repository->setUrl(Uri::new('https://url/'));
        $path = '/foo/bar/';

        $this->builderFactory->expects($this->once())->method('createBranch')->willThrowException(new RepositoryException());

        static::assertFalse($this->service->tryDeleteBranch($repository, $path));
    }
}

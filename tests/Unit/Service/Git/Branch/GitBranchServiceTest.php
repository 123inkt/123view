<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\Branch;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Git\GitRepository;
use DR\GitCommitNotification\Service\Git\Branch\GitBranchCommandBuilder;
use DR\GitCommitNotification\Service\Git\Branch\GitBranchService;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use DR\GitCommitNotification\Service\Git\GitCommandBuilderFactory;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\Branch\GitBranchService
 * @covers ::__construct
 */
class GitBranchServiceTest extends AbstractTestCase
{
    private CacheableGitRepositoryService&MockObject $repositoryService;
    private GitCommandBuilderFactory&MockObject      $builderFactory;
    private GitBranchService                         $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->repositoryService = $this->createMock(CacheableGitRepositoryService::class);
        $this->builderFactory    = $this->createMock(GitCommandBuilderFactory::class);
        $this->service           = new GitBranchService($this->repositoryService, $this->builderFactory);
    }

    /**
     * @covers ::deleteBranch
     * @throws RepositoryException
     */
    public function testDeleteBranch(): void
    {
        $repository = new Repository();
        $repository->setUrl('https://url/');
        $path = '/foo/bar/';

        $builder = $this->createMock(GitBranchCommandBuilder::class);
        $builder->expects(self::once())->method('delete')->with($path)->willReturnSelf();
        $this->builderFactory->expects(self::once())->method('createBranch')->willReturn($builder);

        $git = $this->createMock(GitRepository::class);
        $git->expects(self::once())->method('execute')->with($builder)->willReturn('output');
        $this->repositoryService->expects(self::once())->method('getRepository')->with('https://url/')->willReturn($git);

        $this->service->deleteBranch($repository, $path);
    }

    /**
     * @covers ::tryDeleteBranch
     */
    public function testTryDeleteBranch(): void
    {
        $repository = new Repository();
        $repository->setUrl('https://url/');
        $path = '/foo/bar/';

        $builder = $this->createMock(GitBranchCommandBuilder::class);
        $builder->expects(self::once())->method('delete')->with($path)->willReturnSelf();
        $this->builderFactory->expects(self::once())->method('createBranch')->willReturn($builder);

        $git = $this->createMock(GitRepository::class);
        $git->expects(self::once())->method('execute')->with($builder)->willReturn('output');
        $this->repositoryService->expects(self::once())->method('getRepository')->with('https://url/')->willReturn($git);

        static::assertTrue($this->service->tryDeleteBranch($repository, $path));
    }

    /**
     * @covers ::tryDeleteBranch
     */
    public function testTryDeleteBranchCaptureException(): void
    {
        $repository = new Repository();
        $repository->setUrl('https://url/');
        $path = '/foo/bar/';

        $this->builderFactory->expects(self::once())->method('createBranch')->willThrowException(new RepositoryException());

        static::assertFalse($this->service->tryDeleteBranch($repository, $path));
    }
}

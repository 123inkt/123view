<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Branch;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Git\GitRepository;
use DR\Review\Service\Git\Branch\GitBranchCommandBuilder;
use DR\Review\Service\Git\Branch\GitBranchService;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Tests\AbstractTestCase;
use League\Uri\Uri;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Service\Git\Branch\GitBranchService
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
        $repository->setUrl(Uri::createFromString('https://url/'));
        $path = '/foo/bar/';

        $builder = $this->createMock(GitBranchCommandBuilder::class);
        $builder->expects(self::once())->method('delete')->with($path)->willReturnSelf();
        $this->builderFactory->expects(self::once())->method('createBranch')->willReturn($builder);

        $git = $this->createMock(GitRepository::class);
        $git->expects(self::once())->method('execute')->with($builder)->willReturn('output');
        $this->repositoryService->expects(self::once())->method('getRepository')->with($repository)->willReturn($git);

        $this->service->deleteBranch($repository, $path);
    }

    /**
     * @covers ::tryDeleteBranch
     */
    public function testTryDeleteBranch(): void
    {
        $repository = new Repository();
        $repository->setUrl(Uri::createFromString('https://url/'));
        $path = '/foo/bar/';

        $builder = $this->createMock(GitBranchCommandBuilder::class);
        $builder->expects(self::once())->method('delete')->with($path)->willReturnSelf();
        $this->builderFactory->expects(self::once())->method('createBranch')->willReturn($builder);

        $git = $this->createMock(GitRepository::class);
        $git->expects(self::once())->method('execute')->with($builder)->willReturn('output');
        $this->repositoryService->expects(self::once())->method('getRepository')->with($repository)->willReturn($git);

        static::assertTrue($this->service->tryDeleteBranch($repository, $path));
    }

    /**
     * @covers ::tryDeleteBranch
     */
    public function testTryDeleteBranchCaptureException(): void
    {
        $repository = new Repository();
        $repository->setUrl(Uri::createFromString('https://url/'));
        $path = '/foo/bar/';

        $this->builderFactory->expects(self::once())->method('createBranch')->willThrowException(new RepositoryException());

        static::assertFalse($this->service->tryDeleteBranch($repository, $path));
    }
}

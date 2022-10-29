<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\Add;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Git\GitRepository;
use DR\GitCommitNotification\Service\Git\Add\GitAddCommandBuilder;
use DR\GitCommitNotification\Service\Git\Add\GitAddService;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use DR\GitCommitNotification\Service\Git\GitCommandBuilderFactory;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\Add\GitAddService
 * @covers ::__construct
 */
class GitAddServiceTest extends AbstractTestCase
{
    private CacheableGitRepositoryService&MockObject $repositoryService;
    private GitCommandBuilderFactory&MockObject      $builderFactory;
    private GitAddService                            $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->repositoryService = $this->createMock(CacheableGitRepositoryService::class);
        $this->builderFactory    = $this->createMock(GitCommandBuilderFactory::class);
        $this->service           = new GitAddService($this->repositoryService, $this->builderFactory);
    }

    /**
     * @covers ::add
     * @throws RepositoryException
     */
    public function testAdd(): void
    {
        $repository = new Repository();
        $repository->setUrl('https://url/');
        $path = '/foo/bar/';

        $builder = $this->createMock(GitAddCommandBuilder::class);
        $builder->expects(self::once())->method('setPath')->with($path)->willReturnSelf();
        $this->builderFactory->expects(self::once())->method('createAdd')->willReturn($builder);

        $git = $this->createMock(GitRepository::class);
        $git->expects(self::once())->method('execute')->with($builder)->willReturn('output');
        $this->repositoryService->expects(self::once())->method('getRepository')->with('https://url/')->willReturn($git);

        $this->service->add($repository, $path);
    }
}

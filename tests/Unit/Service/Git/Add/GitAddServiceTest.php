<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Add;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Git\GitRepository;
use DR\Review\Service\Git\Add\GitAddCommandBuilder;
use DR\Review\Service\Git\Add\GitAddService;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Tests\AbstractTestCase;
use League\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(GitAddService::class)]
class GitAddServiceTest extends AbstractTestCase
{
    private CacheableGitRepositoryService&MockObject $repositoryService;
    private GitCommandBuilderFactory&MockObject $builderFactory;
    private GitAddService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->repositoryService = $this->createMock(CacheableGitRepositoryService::class);
        $this->builderFactory    = $this->createMock(GitCommandBuilderFactory::class);
        $this->service           = new GitAddService($this->repositoryService, $this->builderFactory);
    }

    /**
     * @throws RepositoryException
     */
    public function testAdd(): void
    {
        $repository = new Repository();
        $repository->setUrl(Uri::new('https://url/'));
        $path = '/foo/bar/';

        $builder = $this->createMock(GitAddCommandBuilder::class);
        $builder->expects($this->once())->method('setPath')->with($path)->willReturnSelf();
        $this->builderFactory->expects($this->once())->method('createAdd')->willReturn($builder);

        $git = $this->createMock(GitRepository::class);
        $git->expects($this->once())->method('execute')->with($builder)->willReturn('output');
        $this->repositoryService->expects($this->once())->method('getRepository')->with($repository)->willReturn($git);

        $this->service->add($repository, $path);
    }
}

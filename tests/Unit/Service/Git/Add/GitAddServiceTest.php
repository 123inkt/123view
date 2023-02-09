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
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Service\Git\Add\GitAddService
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
        $repository->setUrl(Uri::createFromString('https://url/'));
        $path = '/foo/bar/';

        $builder = $this->createMock(GitAddCommandBuilder::class);
        $builder->expects(self::once())->method('setPath')->with($path)->willReturnSelf();
        $this->builderFactory->expects(self::once())->method('createAdd')->willReturn($builder);

        $git = $this->createMock(GitRepository::class);
        $git->expects(self::once())->method('execute')->with($builder)->willReturn('output');
        $this->repositoryService->expects(self::once())->method('getRepository')->with($repository)->willReturn($git);

        $this->service->add($repository, $path);
    }
}

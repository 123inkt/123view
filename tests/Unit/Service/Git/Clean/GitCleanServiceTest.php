<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Clean;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Git\GitRepository;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\Clean\GitCleanCommandBuilder;
use DR\Review\Service\Git\Clean\GitCleanService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Service\Git\Clean\GitCleanService
 * @covers ::__construct
 */
class GitCleanServiceTest extends AbstractTestCase
{
    private CacheableGitRepositoryService&MockObject $repositoryService;
    private GitCommandBuilderFactory&MockObject      $builderFactory;
    private GitCleanService                          $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryService = $this->createMock(CacheableGitRepositoryService::class);
        $this->builderFactory    = $this->createMock(GitCommandBuilderFactory::class);
        $this->service           = new GitCleanService($this->repositoryService, $this->builderFactory);
    }

    /**
     * @covers ::forceClean
     * @throws RepositoryException
     */
    public function testForceClean(): void
    {
        $repository = new Repository();
        $repository->setUrl('https://example.com');

        $builder = $this->createMock(GitCleanCommandBuilder::class);
        $builder->expects(self::once())->method('force')->willReturnSelf();
        $builder->expects(self::once())->method('skipIgnoreRules')->willReturnSelf();
        $builder->expects(self::once())->method('recurseDirectories')->willReturnSelf();
        $this->builderFactory->expects(self::once())->method('createClean')->willReturn($builder);

        $gitRepository = $this->createMock(GitRepository::class);
        $gitRepository->expects(static::once())->method('execute')->with($builder)->willReturn('output');
        $this->repositoryService->expects(static::once())->method('getRepository')->with($repository)->willReturn($gitRepository);

        $this->service->forceClean($repository);
    }
}

<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\Reset;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Git\GitRepository;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use DR\GitCommitNotification\Service\Git\GitCommandBuilderFactory;
use DR\GitCommitNotification\Service\Git\Reset\GitResetCommandBuilder;
use DR\GitCommitNotification\Service\Git\Reset\GitResetService;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\Reset\GitResetService
 * @covers ::__construct
 */
class GitResetServiceTest extends AbstractTestCase
{
    private CacheableGitRepositoryService&MockObject $repositoryService;
    private GitCommandBuilderFactory&MockObject      $builderFactory;
    private GitResetService                          $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->repositoryService = $this->createMock(CacheableGitRepositoryService::class);
        $this->builderFactory    = $this->createMock(GitCommandBuilderFactory::class);
        $this->service           = new GitResetService($this->repositoryService, $this->builderFactory);
    }

    /**
     * @covers ::resetHard
     * @throws RepositoryException
     */
    public function testResetHard(): void
    {
        $repository = new Repository();
        $repository->setUrl('https://example.com');

        $builder = $this->createMock(GitResetCommandBuilder::class);
        $builder->expects(self::once())->method('hard')->willReturnSelf();
        $this->builderFactory->expects(self::once())->method('createReset')->willReturn($builder);

        $gitRepository = $this->createMock(GitRepository::class);
        $gitRepository->expects(static::once())->method('execute')->with($builder)->willReturn('output');
        $this->repositoryService->expects(static::once())->method('getRepository')->with('https://example.com')->willReturn($gitRepository);

        $this->service->resetHard($repository);
    }
}

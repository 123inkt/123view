<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Reset;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Git\GitRepository;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\Reset\GitResetCommandBuilder;
use DR\Review\Service\Git\Reset\GitResetService;
use DR\Review\Tests\AbstractTestCase;
use League\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(GitResetService::class)]
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
     * @throws RepositoryException
     */
    public function testResetHard(): void
    {
        $commitHash = '123abc';

        $repository = new Repository();
        $repository->setUrl(Uri::new('https://example.com'));

        $builder = $this->createMock(GitResetCommandBuilder::class);
        $builder->expects(self::once())->method('hard')->willReturnSelf();
        $builder->expects(self::once())->method('commitHash')->with($commitHash)->willReturnSelf();
        $this->builderFactory->expects(self::once())->method('createReset')->willReturn($builder);

        $gitRepository = $this->createMock(GitRepository::class);
        $gitRepository->expects(static::once())->method('execute')->with($builder)->willReturn('output');
        $this->repositoryService->expects(static::once())->method('getRepository')->with($repository)->willReturn($gitRepository);

        $this->service->resetHard($repository, $commitHash);
    }

    /**
     * @throws RepositoryException
     */
    public function testResetSoft(): void
    {
        $commitHash = '123abc';

        $repository = new Repository();
        $repository->setUrl(Uri::new('https://example.com'));

        $builder = $this->createMock(GitResetCommandBuilder::class);
        $builder->expects(self::once())->method('soft')->willReturnSelf();
        $builder->expects(self::once())->method('commitHash')->with($commitHash)->willReturnSelf();
        $this->builderFactory->expects(self::once())->method('createReset')->willReturn($builder);

        $gitRepository = $this->createMock(GitRepository::class);
        $gitRepository->expects(static::once())->method('execute')->with($builder)->willReturn('output');
        $this->repositoryService->expects(static::once())->method('getRepository')->with($repository)->willReturn($gitRepository);

        $this->service->resetSoft($repository, $commitHash);
    }
}

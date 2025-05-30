<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\GarbageCollect;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Git\GitRepository;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GarbageCollect\GitGarbageCollectCommandBuilder;
use DR\Review\Service\Git\GarbageCollect\GitGarbageCollectService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Tests\AbstractTestCase;
use League\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(GitGarbageCollectService::class)]
class GitGarbageCollectServiceTest extends AbstractTestCase
{
    private CacheableGitRepositoryService&MockObject $repositoryService;
    private GitCommandBuilderFactory&MockObject      $commandFactory;
    private GitGarbageCollectService                 $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryService = $this->createMock(CacheableGitRepositoryService::class);
        $this->commandFactory    = $this->createMock(GitCommandBuilderFactory::class);
        $this->service           = new GitGarbageCollectService($this->repositoryService, $this->commandFactory);
    }

    /**
     * @throws RepositoryException
     */
    public function testGarbageCollect(): void
    {
        $repository = new Repository();
        $repository->setUrl(Uri::new('https://example.com'));

        $builder = $this->createMock(GitGarbageCollectCommandBuilder::class);
        $builder->expects($this->once())->method('prune')->with('date')->willReturnSelf();
        $builder->expects($this->once())->method('quiet')->willReturnSelf();
        $this->commandFactory->expects($this->once())->method('createGarbageCollect')->willReturn($builder);

        $gitRepository = $this->createMock(GitRepository::class);
        $gitRepository->expects(static::once())->method('execute')->with($builder)->willReturn('output');
        $this->repositoryService->expects(static::once())->method('getRepository')->with($repository)->willReturn($gitRepository);

        $this->service->garbageCollect($repository, 'date');
    }
}

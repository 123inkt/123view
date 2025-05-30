<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Remote;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Git\GitRepository;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\Remote\GitRemoteCommandBuilder;
use DR\Review\Service\Git\Remote\GitRemoteService;
use DR\Review\Tests\AbstractTestCase;
use League\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(GitRemoteService::class)]
class GitRemoteServiceTest extends AbstractTestCase
{
    private CacheableGitRepositoryService&MockObject $repositoryService;
    private GitCommandBuilderFactory&MockObject $builderFactory;
    private GitRemoteService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->repositoryService = $this->createMock(CacheableGitRepositoryService::class);
        $this->builderFactory    = $this->createMock(GitCommandBuilderFactory::class);
        $this->service           = new GitRemoteService($this->repositoryService, $this->builderFactory);
    }

    /**
     * @throws RepositoryException
     */
    public function testUpdateRemoteUrl(): void
    {
        $repository = new Repository();
        $repository->setUrl(Uri::new('https://example.com'));

        $builder = $this->createMock(GitRemoteCommandBuilder::class);
        $builder->expects($this->once())->method('setUrl')->with('origin', 'https://example.com')->willReturnSelf();
        $this->builderFactory->expects($this->once())->method('createRemote')->willReturn($builder);

        $gitRepository = $this->createMock(GitRepository::class);
        $gitRepository->expects($this->once())->method('execute')->with($builder)->willReturn('output');
        $this->repositoryService->expects($this->once())->method('getRepository')->with($repository)->willReturn($gitRepository);

        $this->service->updateRemoteUrl($repository);
    }
}

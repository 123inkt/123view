<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\DiffTree;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Exception\RepositoryException;
use DR\Review\Git\GitRepository;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\DiffTree\GitDiffTreeCommandBuilder;
use DR\Review\Service\Git\DiffTree\GitDiffTreeService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Tests\AbstractTestCase;
use League\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(GitDiffTreeService::class)]
class GitDiffTreeServiceTest extends AbstractTestCase
{
    private CacheableGitRepositoryService&MockObject $repositoryService;
    private GitCommandBuilderFactory&MockObject      $builderFactory;
    private GitDiffTreeService                       $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->repositoryService = $this->createMock(CacheableGitRepositoryService::class);
        $this->builderFactory    = $this->createMock(GitCommandBuilderFactory::class);
        $this->service           = new GitDiffTreeService($this->repositoryService, $this->builderFactory);
    }

    /**
     * @throws RepositoryException
     */
    public function testGetFilesInRevision(): void
    {
        $repository = new Repository();
        $repository->setUrl(Uri::new('https://foobar.com'));
        $revision = new Revision();
        $revision->setCommitHash('hash');
        $revision->setRepository($repository);

        $commandBuilder = new GitDiffTreeCommandBuilder('git');

        // setup mocks
        $this->builderFactory->expects($this->once())->method('createDiffTree')->willReturn($commandBuilder);
        $gitRepository = $this->createMock(GitRepository::class);
        $gitRepository->expects(static::once())->method('execute')->with($commandBuilder)->willReturn("foo\nbar\n  ");
        $this->repositoryService->expects(static::once())->method('getRepository')->with($repository)->willReturn($gitRepository);

        $files = $this->service->getFilesInRevision($revision);
        static::assertSame(['foo', 'bar'], $files);
    }
}

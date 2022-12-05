<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\DiffTree;

use DR\GitCommitNotification\Entity\Repository\Repository;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Git\GitRepository;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use DR\GitCommitNotification\Service\Git\DiffTree\GitDiffTreeCommandBuilder;
use DR\GitCommitNotification\Service\Git\DiffTree\GitDiffTreeService;
use DR\GitCommitNotification\Service\Git\GitCommandBuilderFactory;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\DiffTree\GitDiffTreeService
 * @covers ::__construct
 */
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
     * @covers ::getFilesInRevision
     * @throws RepositoryException
     */
    public function testGetFilesInRevision(): void
    {
        $repository = new Repository();
        $repository->setUrl('http://foobar.com');
        $revision = new Revision();
        $revision->setCommitHash('hash');
        $revision->setRepository($repository);

        $commandBuilder = new GitDiffTreeCommandBuilder('git');

        // setup mocks
        $this->builderFactory->expects(self::once())->method('createDiffTree')->willReturn($commandBuilder);
        $gitRepository = $this->createMock(GitRepository::class);
        $gitRepository->expects(static::once())->method('execute')->with($commandBuilder)->willReturn("foo\nbar\n  ");
        $this->repositoryService->expects(static::once())->method('getRepository')->with('http://foobar.com')->willReturn($gitRepository);

        $files = $this->service->getFilesInRevision($revision);
        static::assertSame(['foo', 'bar'], $files);
    }
}

<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\Show;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Git\GitRepository;
use DR\GitCommitNotification\Service\Git\GitCommandBuilderFactory;
use DR\GitCommitNotification\Service\Git\GitRepositoryService;
use DR\GitCommitNotification\Service\Git\Show\GitShowCommandBuilder;
use DR\GitCommitNotification\Service\Git\Show\GitShowService;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\Show\GitShowService
 * @covers ::__construct
 */
class GitShowServiceTest extends AbstractTestCase
{
    private GitCommandBuilderFactory&MockObject $builderFactory;
    private GitRepositoryService&MockObject     $repositoryService;
    private GitShowService                      $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->builderFactory    = $this->createMock(GitCommandBuilderFactory::class);
        $this->repositoryService = $this->createMock(GitRepositoryService::class);
        $this->service           = new GitShowService($this->builderFactory, $this->repositoryService);
    }

    /**
     * @covers ::getFileAtRevision
     * @throws RepositoryException
     */
    public function testGetFileAtRevision(): void
    {
        $commandBuilder = $this->createMock(GitShowCommandBuilder::class);
        $gitRepository  = $this->createMock(GitRepository::class);

        $repository = new Repository();
        $repository->setName('name');
        $repository->setUrl('url');
        $revision = new Revision();
        $revision->setCommitHash('hash');
        $revision->setRepository($repository);

        $filePath = 'filepath';

        $commandBuilder->expects(self::once())->method('file')->with('hash', $filePath)->willReturnSelf();
        $this->builderFactory->expects(self::once())->method('createShow')->willReturn($commandBuilder);
        $this->repositoryService->expects(self::once())->method('getRepository')->with('url')->willReturn($gitRepository);
        $gitRepository->expects(static::once())->method('execute')->with($commandBuilder)->willReturn('output');

        static::assertSame('output', $this->service->getFileAtRevision($revision, $filePath));
    }
}

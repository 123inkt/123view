<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\LsTree;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Git\GitRepository;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\GitRepositoryService;
use DR\Review\Service\Git\LsTree\GitLsTreeCommandBuilder;
use DR\Review\Service\Git\LsTree\LsTreeService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(LsTreeService::class)]
class LsTreeServiceTest extends AbstractTestCase
{
    private GitCommandBuilderFactory&MockObject $builderFactory;
    private GitRepositoryService&MockObject     $repositoryService;
    private LsTreeService                       $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builderFactory    = $this->createMock(GitCommandBuilderFactory::class);
        $this->repositoryService = $this->createMock(GitRepositoryService::class);
        $this->service           = new LsTreeService($this->builderFactory, $this->repositoryService);
    }

    public function testListFilesWithoutGlob(): void
    {
        $repository = new Repository();
        $revision   = new Revision();
        $revision->setRepository($repository);
        $revision->setCommitHash('abc123');

        $commandBuilder = $this->createMock(GitLsTreeCommandBuilder::class);
        $gitRepository  = $this->createMock(GitRepository::class);

        $commandBuilder->expects($this->once())->method('nameOnly')->willReturnSelf();
        $commandBuilder->expects($this->once())->method('hash')->with('abc123')->willReturnSelf();
        $commandBuilder->expects($this->once())->method('file')->with('path/to/file.txt')->willReturnSelf();
        $commandBuilder->expects($this->never())->method('recursive');

        $this->builderFactory->expects($this->once())->method('createLsTree')->willReturn($commandBuilder);
        $this->repositoryService->expects($this->once())->method('getRepository')->with($repository)->willReturn($gitRepository);
        $gitRepository->expects($this->once())->method('execute')->with($commandBuilder)->willReturn("path/to/file.txt\n");

        $result = $this->service->listFiles($revision, '/path/to/file.txt');

        static::assertSame(['path/to/file.txt'], $result);
    }

    public function testListFilesWithGlob(): void
    {
        $repository = new Repository();
        $revision   = new Revision();
        $revision->setRepository($repository);
        $revision->setCommitHash('abc123');

        $commandBuilder = $this->createMock(GitLsTreeCommandBuilder::class);
        $gitRepository  = $this->createMock(GitRepository::class);

        $commandBuilder->expects($this->once())->method('nameOnly')->willReturnSelf();
        $commandBuilder->expects($this->once())->method('hash')->with('abc123')->willReturnSelf();
        $commandBuilder->expects($this->once())->method('file')->with('src/')->willReturnSelf();
        $commandBuilder->expects($this->once())->method('recursive')->willReturnSelf();

        $this->builderFactory->expects($this->once())->method('createLsTree')->willReturn($commandBuilder);
        $this->repositoryService->expects($this->once())->method('getRepository')->with($repository)->willReturn($gitRepository);
        $gitRepository->expects($this->once())->method('execute')
            ->with($commandBuilder)
            ->willReturn("src/file.php\nsrc/other.txt\nsrc/nested/file.php\n");

        $result = $this->service->listFiles($revision, 'src/**/*.php');

        static::assertSame(['src/nested/file.php'], array_values($result));
    }
}

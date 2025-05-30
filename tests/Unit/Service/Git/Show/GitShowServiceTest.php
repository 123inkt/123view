<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Show;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Exception\RepositoryException;
use DR\Review\Git\GitRepository;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\GitRepositoryService;
use DR\Review\Service\Git\Log\FormatPatternFactory;
use DR\Review\Service\Git\Show\GitShowCommandBuilder;
use DR\Review\Service\Git\Show\GitShowService;
use DR\Review\Service\Parser\GitLogParser;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use League\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(GitShowService::class)]
class GitShowServiceTest extends AbstractTestCase
{
    private GitCommandBuilderFactory&MockObject $builderFactory;
    private GitRepositoryService&MockObject     $repositoryService;
    private GitLogParser&MockObject             $logParser;
    private FormatPatternFactory&MockObject     $patternFactory;
    private GitShowService                      $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->builderFactory    = $this->createMock(GitCommandBuilderFactory::class);
        $this->repositoryService = $this->createMock(GitRepositoryService::class);
        $this->logParser         = $this->createMock(GitLogParser::class);
        $this->patternFactory    = $this->createMock(FormatPatternFactory::class);
        $this->service           = new GitShowService($this->builderFactory, $this->repositoryService, $this->logParser, $this->patternFactory);
    }

    /**
     * @throws Exception
     */
    public function testGetCommitFromHash(): void
    {
        $commandBuilder = $this->createMock(GitShowCommandBuilder::class);
        $gitRepository  = $this->createMock(GitRepository::class);

        $repository = new Repository();
        $repository->setName('name');
        $repository->setUrl(Uri::new('url'));
        $commit = $this->createCommit();

        $commandBuilder->expects($this->once())->method('startPoint')->with('hash')->willReturnSelf();
        $commandBuilder->expects($this->once())->method('noPatch')->willReturnSelf();
        $commandBuilder->expects($this->once())->method('format')->with('pattern')->willReturnSelf();
        $this->patternFactory->expects($this->once())->method('createPattern')->willReturn('pattern');
        $this->builderFactory->expects($this->once())->method('createShow')->willReturn($commandBuilder);
        $this->repositoryService->expects($this->once())->method('getRepository')->with($repository)->willReturn($gitRepository);
        $gitRepository->expects(static::once())->method('execute')->with($commandBuilder)->willReturn('output');
        $this->logParser->expects($this->once())->method('parse')->with($repository, 'output')->willReturn([$commit]);

        static::assertSame($commit, $this->service->getCommitFromHash($repository, 'hash'));
    }

    /**
     * @throws RepositoryException
     */
    public function testFileContentsWithBinaryData(): void
    {
        $repository = new Repository();
        $revision   = new Revision();
        $revision->setRepository($repository);
        $revision->setCommitHash('hash');

        $commandBuilder = $this->createMock(GitShowCommandBuilder::class);
        $gitRepository  = $this->createMock(GitRepository::class);

        $commandBuilder->expects($this->once())->method('file')->with('hash', 'file')->willReturnSelf();
        $commandBuilder->expects($this->once())->method('base64encode')->willReturnSelf();
        $this->builderFactory->expects($this->once())->method('createShow')->willReturn($commandBuilder);

        $this->repositoryService->expects($this->once())->method('getRepository')->with($repository)->willReturn($gitRepository);
        $gitRepository->expects(static::once())->method('execute')->with($commandBuilder)->willReturn(base64_encode('output'));

        static::assertSame('output', $this->service->getFileContents($revision, 'file', true));
    }

    /**
     * @throws RepositoryException
     */
    public function testFileContentsWithTextData(): void
    {
        $repository = new Repository();
        $revision   = new Revision();
        $revision->setRepository($repository);
        $revision->setCommitHash('hash');

        $commandBuilder = $this->createMock(GitShowCommandBuilder::class);
        $gitRepository  = $this->createMock(GitRepository::class);

        $commandBuilder->expects($this->once())->method('file')->with('hash', 'file')->willReturnSelf();
        $commandBuilder->expects(self::never())->method('base64encode');
        $this->builderFactory->expects($this->once())->method('createShow')->willReturn($commandBuilder);

        $this->repositoryService->expects($this->once())->method('getRepository')->with($repository)->willReturn($gitRepository);
        $gitRepository->expects(static::once())->method('execute')->with($commandBuilder)->willReturn('output');

        static::assertSame('output', $this->service->getFileContents($revision, 'file'));
    }
}

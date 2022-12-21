<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Show;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Git\GitRepository;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\GitRepositoryService;
use DR\Review\Service\Git\Log\FormatPatternFactory;
use DR\Review\Service\Git\Show\GitShowCommandBuilder;
use DR\Review\Service\Git\Show\GitShowService;
use DR\Review\Service\Parser\GitLogParser;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Service\Git\Show\GitShowService
 * @covers ::__construct
 */
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
     * @covers ::getCommitFromHash
     * @throws Exception
     */
    public function testGetCommitFromHash(): void
    {
        $commandBuilder = $this->createMock(GitShowCommandBuilder::class);
        $gitRepository  = $this->createMock(GitRepository::class);

        $repository = new Repository();
        $repository->setName('name');
        $repository->setUrl('url');
        $commit = $this->createCommit();

        $commandBuilder->expects(self::once())->method('startPoint')->with('hash')->willReturnSelf();
        $commandBuilder->expects(self::once())->method('noPatch')->willReturnSelf();
        $commandBuilder->expects(self::once())->method('format')->with('pattern')->willReturnSelf();
        $this->patternFactory->expects(self::once())->method('createPattern')->willReturn('pattern');
        $this->builderFactory->expects(self::once())->method('createShow')->willReturn($commandBuilder);
        $this->repositoryService->expects(self::once())->method('getRepository')->with('url')->willReturn($gitRepository);
        $gitRepository->expects(static::once())->method('execute')->with($commandBuilder)->willReturn('output');
        $this->logParser->expects(self::once())->method('parse')->with($repository, 'output')->willReturn([$commit]);

        static::assertSame($commit, $this->service->getCommitFromHash($repository, 'hash'));
    }
}

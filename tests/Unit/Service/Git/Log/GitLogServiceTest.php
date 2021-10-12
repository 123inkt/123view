<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\Log;

use DR\GitCommitNotification\Entity\Config\Configuration;
use DR\GitCommitNotification\Entity\Config\Repositories;
use DR\GitCommitNotification\Entity\Config\RepositoryReferences;
use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Entity\Git\Commit;
use DR\GitCommitNotification\Git\GitRepository;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use DR\GitCommitNotification\Service\Git\Log\GitLogCommandBuilder;
use DR\GitCommitNotification\Service\Git\Log\GitLogCommandFactory;
use DR\GitCommitNotification\Service\Git\Log\GitLogService;
use DR\GitCommitNotification\Service\Parser\GitLogParser;
use DR\GitCommitNotification\Tests\AbstractTest;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\Log\GitLogService
 * @covers ::__construct
 */
class GitLogServiceTest extends AbstractTest
{
    /** @var CacheableGitRepositoryService|MockObject */
    private CacheableGitRepositoryService $repositoryService;
    /** @var GitLogCommandFactory|MockObject */
    private GitLogCommandFactory $commandFactory;
    /** @var GitLogParser|MockObject */
    private GitLogParser  $logParser;
    private GitLogService $logFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryService = $this->createMock(CacheableGitRepositoryService::class);
        $this->commandFactory    = $this->createMock(GitLogCommandFactory::class);
        $this->logParser         = $this->createMock(GitLogParser::class);
        $this->logFactory        = new GitLogService($this->log, $this->repositoryService, $this->commandFactory, $this->logParser);
    }

    /**
     * @covers ::getCommits
     * @throws Exception
     */
    public function testGetCommits(): void
    {
        // setup config
        $repositoryConfig            = $this->createRepository("example", "https://example.com");
        $configuration               = new Configuration();
        $configuration->repositories = new Repositories();
        $configuration->repositories->addRepository($repositoryConfig);

        // setup rule
        $rule               = new Rule();
        $rule->config       = $configuration;
        $rule->repositories = new RepositoryReferences();
        $rule->repositories->addRepository($this->createRepositoryReference('example'));
        $repository     = $this->createMock(GitRepository::class);
        $commandBuilder = new GitLogCommandBuilder();
        $commits        = [$this->createMock(Commit::class), $this->createMock(Commit::class)];

        // setup mocks
        $this->repositoryService->expects(static::once())->method('getRepository')->with('https://example.com')->willReturn($repository);
        $this->commandFactory->expects(static::once())->method('fromRule')->with($rule)->willReturn($commandBuilder);
        $repository->expects(static::once())->method('execute')->with($commandBuilder)->willReturn('output');
        $this->logParser->expects(static::once())->method('parse')->with($repositoryConfig, 'output')->willReturn($commits);

        // execute test
        $actual = $this->logFactory->getCommits($rule);
        static::assertSame(array_reverse($commits), $actual);
    }
}

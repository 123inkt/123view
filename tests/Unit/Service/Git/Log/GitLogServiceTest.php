<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\Log;

use DateInterval;
use DatePeriod;
use DateTime;
use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Entity\Config\RuleConfiguration;
use DR\GitCommitNotification\Entity\Git\Commit;
use DR\GitCommitNotification\Git\GitRepository;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use DR\GitCommitNotification\Service\Git\GitCommandBuilderFactory;
use DR\GitCommitNotification\Service\Git\Log\FormatPatternFactory;
use DR\GitCommitNotification\Service\Git\Log\GitLogCommandBuilder;
use DR\GitCommitNotification\Service\Git\Log\GitLogCommandFactory;
use DR\GitCommitNotification\Service\Git\Log\GitLogService;
use DR\GitCommitNotification\Service\Parser\GitLogParser;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\Tests\Helper\MockGitRepositoryLockManager;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\Log\GitLogService
 * @covers ::__construct
 */
class GitLogServiceTest extends AbstractTestCase
{
    private CacheableGitRepositoryService&MockObject $repositoryService;
    private GitLogCommandFactory&MockObject          $commandFactory;
    private GitLogParser&MockObject                  $logParser;
    private GitLogService                            $logFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryService = $this->createMock(CacheableGitRepositoryService::class);
        $commandBuilderFactory   = $this->createMock(GitCommandBuilderFactory::class);
        $this->commandFactory    = $this->createMock(GitLogCommandFactory::class);
        $patternFactory          = $this->createMock(FormatPatternFactory::class);
        $this->logParser         = $this->createMock(GitLogParser::class);
        $this->logFactory        = new GitLogService(
            $this->repositoryService,
            $commandBuilderFactory,
            new MockGitRepositoryLockManager(),
            $this->commandFactory,
            $patternFactory,
            $this->logParser
        );
    }

    /**
     * @covers ::getCommits
     * @throws Exception
     */
    public function testGetCommits(): void
    {
        // setup config
        $repositoryConfig = $this->createRepository("example", "https://example.com");

        // setup rule
        $rule = new Rule();
        $rule->addRepository($repositoryConfig);
        $config         = new RuleConfiguration(new DatePeriod(new DateTime(), new DateInterval('PT1H'), new DateTime()), [], $rule);
        $repository     = $this->createMock(GitRepository::class);
        $commandBuilder = new GitLogCommandBuilder('git');
        $commits        = [$this->createMock(Commit::class), $this->createMock(Commit::class)];

        // setup mocks
        $this->repositoryService->expects(static::once())->method('getRepository')->with('https://example.com')->willReturn($repository);
        $this->commandFactory->expects(static::once())->method('fromRule')->with($config)->willReturn($commandBuilder);
        $repository->expects(static::once())->method('execute')->with($commandBuilder)->willReturn('output');
        $this->logParser->expects(static::once())->method('parse')->with($repositoryConfig, 'output')->willReturn($commits);

        // execute test
        $actual = $this->logFactory->getCommits($config);
        static::assertSame(array_reverse($commits), $actual);
    }
}

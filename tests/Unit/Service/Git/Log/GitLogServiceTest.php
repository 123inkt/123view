<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\Log;

use DateInterval;
use DatePeriod;
use DateTime;
use DR\GitCommitNotification\Entity\Git\Commit;
use DR\GitCommitNotification\Entity\Notification\Rule;
use DR\GitCommitNotification\Entity\Notification\RuleConfiguration;
use DR\GitCommitNotification\Entity\Repository\Repository;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Git\GitRepository;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use DR\GitCommitNotification\Service\Git\GitCommandBuilderFactory;
use DR\GitCommitNotification\Service\Git\GitRepositoryService;
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
    private CacheableGitRepositoryService&MockObject $cacheRepositoryService;
    private GitRepositoryService&MockObject          $repositoryService;
    private GitCommandBuilderFactory&MockObject      $commandBuilderFactory;
    private GitLogCommandFactory&MockObject          $commandFactory;
    private FormatPatternFactory&MockObject          $patternFactory;
    private GitLogParser&MockObject                  $logParser;
    private GitLogService                            $logFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheRepositoryService = $this->createMock(CacheableGitRepositoryService::class);
        $this->repositoryService      = $this->createMock(GitRepositoryService::class);
        $this->commandBuilderFactory  = $this->createMock(GitCommandBuilderFactory::class);
        $this->commandFactory         = $this->createMock(GitLogCommandFactory::class);
        $this->patternFactory         = $this->createMock(FormatPatternFactory::class);
        $this->logParser              = $this->createMock(GitLogParser::class);
        $this->logFactory             = new GitLogService(
            $this->cacheRepositoryService,
            $this->repositoryService,
            $this->commandBuilderFactory,
            new MockGitRepositoryLockManager(),
            $this->commandFactory,
            $this->patternFactory,
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
        $config         = new RuleConfiguration(new DatePeriod(new DateTime(), new DateInterval('PT1H'), new DateTime()), $rule);
        $repository     = $this->createMock(GitRepository::class);
        $commandBuilder = new GitLogCommandBuilder('git');
        $commits        = [$this->createMock(Commit::class), $this->createMock(Commit::class)];

        // setup mocks
        $this->cacheRepositoryService->expects(static::once())->method('getRepository')->with('https://example.com')->willReturn($repository);
        $this->commandFactory->expects(static::once())->method('fromRule')->with($config)->willReturn($commandBuilder);
        $repository->expects(static::once())->method('execute')->with($commandBuilder)->willReturn('output');
        $this->logParser->expects(static::once())->method('parse')->with($repositoryConfig, 'output')->willReturn($commits);

        // execute test
        $actual = $this->logFactory->getCommits($config);
        static::assertSame(array_reverse($commits), $actual);
    }

    /**
     * @covers ::getCommitsSince
     * @throws Exception
     */
    public function testGetCommitsSince(): void
    {
        $limit      = 5;
        $commits    = [$this->createMock(Commit::class), $this->createMock(Commit::class)];
        $repository = new Repository();
        $repository->setUrl('https://example.com');

        $revision = new Revision();
        $revision->setCreateTimestamp(12345678);

        $logBuilder    = $this->createMock(GitLogCommandBuilder::class);
        $gitRepository = $this->createMock(GitRepository::class);

        $this->commandBuilderFactory->expects(self::once())->method('createLog')->willReturn($logBuilder);
        $this->patternFactory->expects(self::once())->method('createPattern')->willReturn('pattern');
        $logBuilder->expects(self::once())->method('noMerges')->willReturnSelf();
        $logBuilder->expects(self::once())->method('remotes')->willReturnSelf();
        $logBuilder->expects(self::once())->method('reverse')->willReturnSelf();
        $logBuilder->expects(self::once())->method('dateOrder')->willReturnSelf();
        $logBuilder->expects(self::once())->method('format')->with('pattern')->willReturnSelf();
        $logBuilder->expects(self::once())->method('since')->willReturnSelf();

        $gitRepository->expects(static::once())->method('execute')->with($logBuilder)->willReturn('output');
        $this->repositoryService->expects(static::once())->method('getRepository')->with('https://example.com', true)->willReturn($gitRepository);
        $this->logParser->expects(static::once())->method('parse')->with($repository, 'output', $limit)->willReturn($commits);

        $this->logFactory->getCommitsSince($repository, $revision, $limit);
    }
}

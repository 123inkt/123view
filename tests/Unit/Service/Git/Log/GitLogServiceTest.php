<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Log;

use DateInterval;
use DatePeriod;
use DateTime;
use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Notification\RuleConfiguration;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Git\FormatPattern;
use DR\Review\Git\GitRepository;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\Log\FormatPatternFactory;
use DR\Review\Service\Git\Log\GitLogCommandBuilder;
use DR\Review\Service\Git\Log\GitLogCommandFactory;
use DR\Review\Service\Git\Log\GitLogService;
use DR\Review\Service\Parser\GitLogParser;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\Tests\Helper\MockGitRepositoryLockManager;
use Exception;
use League\Uri\Uri;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Service\Git\Log\GitLogService
 * @covers ::__construct
 */
class GitLogServiceTest extends AbstractTestCase
{
    private CacheableGitRepositoryService&MockObject $repositoryService;
    private GitCommandBuilderFactory&MockObject      $commandBuilderFactory;
    private GitLogCommandFactory&MockObject          $commandFactory;
    private FormatPatternFactory&MockObject          $patternFactory;
    private GitLogParser&MockObject                  $logParser;
    private GitLogService                            $logFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryService     = $this->createMock(CacheableGitRepositoryService::class);
        $this->commandBuilderFactory = $this->createMock(GitCommandBuilderFactory::class);
        $this->commandFactory        = $this->createMock(GitLogCommandFactory::class);
        $this->patternFactory        = $this->createMock(FormatPatternFactory::class);
        $this->logParser             = $this->createMock(GitLogParser::class);
        $this->logFactory            = new GitLogService(
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
        $repository = $this->createRepository("example", "https://example.com");

        // setup rule
        $rule = new Rule();
        $rule->addRepository($repository);
        $config         = new RuleConfiguration(new DatePeriod(new DateTime(), new DateInterval('PT1H'), new DateTime()), $rule);
        $gitRepository  = $this->createMock(GitRepository::class);
        $commandBuilder = new GitLogCommandBuilder('git');
        $commits        = [$this->createMock(Commit::class), $this->createMock(Commit::class)];

        // setup mocks
        $this->repositoryService->expects(static::once())->method('getRepository')->with($repository)->willReturn($gitRepository);
        $this->commandFactory->expects(static::once())->method('fromRule')->with($config)->willReturn($commandBuilder);
        $gitRepository->expects(static::once())->method('execute')->with($commandBuilder)->willReturn('output');
        $this->logParser->expects(static::once())->method('parse')->with($repository, 'output')->willReturn($commits);

        // execute test
        $actual = $this->logFactory->getCommits($config);
        static::assertSame(array_reverse($commits), $actual);
    }

    /**
     * @covers ::getCommitHashes
     * @throws Exception
     */
    public function testGetCommitHashes(): void
    {
        $repository = new Repository();
        $repository->setUrl(Uri::createFromString('https://example.com'));

        $logBuilder    = $this->createMock(GitLogCommandBuilder::class);
        $gitRepository = $this->createMock(GitRepository::class);

        $this->commandBuilderFactory->expects(self::once())->method('createLog')->willReturn($logBuilder);
        $logBuilder->expects(self::once())->method('noMerges')->willReturnSelf();
        $logBuilder->expects(self::once())->method('remotes')->willReturnSelf();
        $logBuilder->expects(self::once())->method('format')->with(FormatPattern::COMMIT_HASH)->willReturnSelf();

        $gitRepository->expects(static::once())->method('execute')->with($logBuilder)->willReturn(" #line1\nline2\n ");
        $this->repositoryService->expects(static::once())->method('getRepository')->with($repository)->willReturn($gitRepository);

        static::assertSame(['line1', 'line2'], $this->logFactory->getCommitHashes($repository));
    }

    /**
     * @covers ::getCommitsFromRange
     * @throws Exception
     */
    public function testGetCommitsFromRange(): void
    {
        $commits    = [$this->createMock(Commit::class), $this->createMock(Commit::class)];
        $repository = new Repository();
        $repository->setUrl(Uri::createFromString('https://example.com'));

        $logBuilder    = $this->createMock(GitLogCommandBuilder::class);
        $gitRepository = $this->createMock(GitRepository::class);

        $this->commandBuilderFactory->expects(self::once())->method('createLog')->willReturn($logBuilder);
        $this->patternFactory->expects(self::once())->method('createPattern')->willReturn('pattern');
        $logBuilder->expects(self::once())->method('noMerges')->willReturnSelf();
        $logBuilder->expects(self::once())->method('hashRange')->with('foo~1', 'bar')->willReturnSelf();
        $logBuilder->expects(self::once())->method('format')->with('pattern')->willReturnSelf();

        $gitRepository->expects(static::once())->method('execute')->with($logBuilder)->willReturn('output');
        $this->repositoryService->expects(static::once())->method('getRepository')->with($repository)->willReturn($gitRepository);
        $this->logParser->expects(static::once())->method('parse')->with($repository, 'output')->willReturn($commits);

        static::assertSame($commits, $this->logFactory->getCommitsFromRange($repository, 'foo', 'bar'));
    }
}

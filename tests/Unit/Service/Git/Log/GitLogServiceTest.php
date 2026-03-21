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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(GitLogService::class)]
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
     * @throws Exception
     */
    public function testGetCommitsShouldIgnoreInactiveRepositories(): void
    {
        $this->repositoryService->expects($this->never())->method('getRepository');
        $this->commandBuilderFactory->expects($this->never())->method('createLog');
        $this->commandFactory->expects($this->never())->method('fromRule');
        $this->patternFactory->expects($this->never())->method('createPattern');
        $this->logParser->expects($this->never())->method('parse');
        $repository = (new Repository())->setActive(false);
        $rule       = (new Rule())->addRepository($repository);
        $config     = new RuleConfiguration(new DatePeriod(new DateTime(), new DateInterval('PT1H'), new DateTime()), $rule);

        static::assertSame([], $this->logFactory->getCommits($config));
    }

    /**
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
        $commits        = [static::createStub(Commit::class), static::createStub(Commit::class)];

        // setup mocks
        $this->repositoryService->expects($this->once())->method('getRepository')->with($repository)->willReturn($gitRepository);
        $this->commandFactory->expects($this->once())->method('fromRule')->with($config)->willReturn($commandBuilder);
        $gitRepository->expects($this->once())->method('execute')->with($commandBuilder)->willReturn('output');
        $this->logParser->expects($this->once())->method('parse')->with($repository, 'output')->willReturn($commits);
        $this->commandBuilderFactory->expects($this->never())->method('createLog');
        $this->patternFactory->expects($this->never())->method('createPattern');

        // execute test
        $actual = $this->logFactory->getCommits($config);
        static::assertSame(array_reverse($commits), $actual);
    }

    /**
     * @throws Exception
     */
    public function testGetCommitHashes(): void
    {
        $repository = new Repository();
        $repository->setUrl(Uri::new('https://example.com'));

        $logBuilder    = $this->createMock(GitLogCommandBuilder::class);
        $gitRepository = $this->createMock(GitRepository::class);

        $this->commandBuilderFactory->expects($this->once())->method('createLog')->willReturn($logBuilder);
        $logBuilder->expects($this->once())->method('noMerges')->willReturnSelf();
        $logBuilder->expects($this->once())->method('remotes')->willReturnSelf();
        $logBuilder->expects($this->once())->method('format')->with(FormatPattern::COMMIT_HASH)->willReturnSelf();

        $gitRepository->expects($this->once())->method('execute')->with($logBuilder)->willReturn(" #line1\nline2\n ");
        $this->repositoryService->expects($this->once())->method('getRepository')->with($repository)->willReturn($gitRepository);
        $this->commandFactory->expects($this->never())->method('fromRule');
        $this->patternFactory->expects($this->never())->method('createPattern');
        $this->logParser->expects($this->never())->method('parse');

        static::assertSame(['line1', 'line2'], $this->logFactory->getCommitHashes($repository));
    }

    /**
     * @throws Exception
     */
    public function testGetCommitsFromRange(): void
    {
        $commits    = [static::createStub(Commit::class), static::createStub(Commit::class)];
        $repository = new Repository();
        $repository->setUrl(Uri::new('https://example.com'));

        $logBuilder    = $this->createMock(GitLogCommandBuilder::class);
        $gitRepository = $this->createMock(GitRepository::class);

        $this->commandBuilderFactory->expects($this->once())->method('createLog')->willReturn($logBuilder);
        $this->patternFactory->expects($this->once())->method('createPattern')->willReturn('pattern');
        $logBuilder->expects($this->once())->method('noMerges')->willReturnSelf();
        $logBuilder->expects($this->once())->method('hashRange')->with('foo~1', 'bar')->willReturnSelf();
        $logBuilder->expects($this->once())->method('format')->with('pattern')->willReturnSelf();

        $gitRepository->expects($this->once())->method('execute')->with($logBuilder)->willReturn('output');
        $this->repositoryService->expects($this->once())->method('getRepository')->with($repository)->willReturn($gitRepository);
        $this->logParser->expects($this->once())->method('parse')->with($repository, 'output')->willReturn($commits);
        $this->commandFactory->expects($this->never())->method('fromRule');

        static::assertSame($commits, $this->logFactory->getCommitsFromRange($repository, 'foo', 'bar'));
    }
}

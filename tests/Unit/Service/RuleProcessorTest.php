<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service;

use DateInterval;
use DatePeriod;
use DateTime;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Notification\Filter;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Notification\RuleConfiguration;
use DR\Review\Event\CommitEvent;
use DR\Review\Service\Filter\CommitFilter;
use DR\Review\Service\Git\Commit\CommitBundler;
use DR\Review\Service\Git\Diff\GitDiffService;
use DR\Review\Service\Git\Diff\UnifiedDiffEmphasizer;
use DR\Review\Service\Git\Log\GitLogService;
use DR\Review\Service\RuleProcessor;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

#[CoversClass(RuleProcessor::class)]
class RuleProcessorTest extends AbstractTestCase
{
    private GitLogService&MockObject            $gitLogService;
    private UnifiedDiffEmphasizer&MockObject    $diffEmphasizer;
    private GitDiffService&MockObject           $diffService;
    private CommitFilter&MockObject             $commitFilter;
    private CommitBundler&MockObject            $commitBundler;
    private EventDispatcherInterface&MockObject $dispatcher;
    private RuleProcessor                       $ruleProcessor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gitLogService  = $this->createMock(GitLogService::class);
        $this->diffEmphasizer = $this->createMock(UnifiedDiffEmphasizer::class);
        $this->diffService    = $this->createMock(GitDiffService::class);
        $this->commitFilter   = $this->createMock(CommitFilter::class);
        $this->commitBundler  = $this->createMock(CommitBundler::class);
        $this->dispatcher     = $this->createMock(EventDispatcherInterface::class);

        $this->ruleProcessor = new RuleProcessor(
            $this->logger,
            $this->gitLogService,
            $this->diffEmphasizer,
            $this->diffService,
            $this->commitFilter,
            $this->commitBundler,
            $this->dispatcher
        );
    }

    /**
     * @throws Throwable
     */
    public function testProcessRule(): void
    {
        $rule = new Rule();
        $rule->setName('foobar');
        $config  = new RuleConfiguration(new DatePeriod(new DateTime(), new DateInterval('PT1H'), new DateTime()), $rule);
        $commit  = $this->createCommit(files: [new DiffFile()]);
        $commits = [$commit];

        $this->gitLogService->expects($this->once())->method('getCommits')->with($config)->willReturn($commits);
        $this->diffEmphasizer->expects($this->once())->method('emphasizeFile');
        $this->commitBundler->expects($this->once())->method('bundle')->with($commits)->willReturn($commits);
        $this->diffService->expects($this->once())->method('getBundledDiff')->with($rule, $commit);
        $this->dispatcher->expects($this->once())->method('dispatch')->with(static::isInstanceOf(CommitEvent::class));
        $this->commitFilter->expects($this->never())->method('exclude');

        static::assertSame($commits, $this->ruleProcessor->processRule($config));
    }

    /**
     * @throws Throwable
     */
    public function testProcessRuleWithExclusionAndInclusions(): void
    {
        $excludeFilter = (new Filter())->setInclusion(false);
        $includeFilter = (new Filter())->setInclusion(true);
        $rule          = (new Rule())->setName('foobar')->addFilter($excludeFilter)->addFilter($includeFilter);
        $config        = new RuleConfiguration(new DatePeriod(new DateTime(), new DateInterval('PT1H'), new DateTime()), $rule);
        $commit        = $this->createCommit();
        $commits       = [$commit];

        $this->gitLogService->expects($this->once())->method('getCommits')->with($config)->willReturn($commits);
        $this->commitBundler->expects($this->once())->method('bundle')->with($commits)->willReturn($commits);
        $this->diffService->expects($this->once())->method('getBundledDiff')->with($rule, $commit)->willReturn($commit);
        $this->commitFilter
            ->expects($this->once())
            ->method('exclude')
            ->with($commits, static::callback(static fn($collection) => $collection->contains($excludeFilter)))
            ->willReturn($commits);
        $this->commitFilter
            ->expects($this->once())
            ->method('include')
            ->with($commits, static::callback(static fn($collection) => $collection->contains($includeFilter)))
            ->willReturn($commits);
        $this->dispatcher->expects($this->once())->method('dispatch')->with(static::isInstanceOf(CommitEvent::class));
        $this->diffEmphasizer->expects($this->never())->method('emphasizeFile');

        static::assertSame($commits, $this->ruleProcessor->processRule($config));
    }

    /**
     * @throws Throwable
     */
    public function testProcessRuleShouldNotSendMailOnNoCommits(): void
    {
        $rule = new Rule();
        $rule->setName('foobar');
        $config = new RuleConfiguration(new DatePeriod(new DateTime(), new DateInterval('PT1H'), new DateTime()), $rule);

        $this->gitLogService->expects($this->once())->method('getCommits')->with($config)->willReturn([]);
        $this->commitBundler->expects($this->once())->method('bundle')->with([])->willReturn([]);
        $this->diffService->expects($this->never())->method('getBundledDiff');
        $this->dispatcher->expects($this->never())->method('dispatch');
        $this->diffEmphasizer->expects($this->never())->method('emphasizeFile');
        $this->commitFilter->expects($this->never())->method('exclude');

        static::assertSame([], $this->ruleProcessor->processRule($config));
    }
}

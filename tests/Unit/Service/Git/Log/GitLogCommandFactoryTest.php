<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Log;

use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use DR\Review\Entity\Notification\Frequency;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Notification\RuleConfiguration;
use DR\Review\Entity\Notification\RuleOptions;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\Log\FormatPatternFactory;
use DR\Review\Service\Git\Log\GitLogCommandBuilder;
use DR\Review\Service\Git\Log\GitLogCommandFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(GitLogCommandFactory::class)]
class GitLogCommandFactoryTest extends AbstractTestCase
{
    private GitLogCommandBuilder&MockObject $commandBuilder;
    private GitLogCommandFactory            $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commandBuilder = $this->createMock(GitLogCommandBuilder::class);
        $factory              = $this->createMock(GitCommandBuilderFactory::class);
        $factory->method('createLog')->willReturn($this->commandBuilder);
        $this->factory = new GitLogCommandFactory($factory, new FormatPatternFactory());
    }

    public function testFromRuleWithMinimalOptions(): void
    {
        $rule = new Rule();
        $rule->setRuleOptions(
            (new RuleOptions())
                ->setFrequency(Frequency::ONCE_PER_DAY)
                ->setIgnoreSpaceAtEol(false)
                ->setExcludeMergeCommits(false)
        );
        $startDate = new DateTimeImmutable('2021-10-18 21:05:00');
        $endDate   = new DateTimeImmutable('2021-10-18 22:05:00');
        $period    = new DatePeriod($startDate, new DateInterval('PT1H'), $endDate);
        $config    = new RuleConfiguration($period, $rule);

        $this->commandBuilder->expects($this->once())->method('remotes')->willReturnSelf();
        $this->commandBuilder->expects($this->once())->method('topoOrder')->willReturnSelf();
        $this->commandBuilder->expects($this->once())->method('patch')->willReturnSelf();
        $this->commandBuilder->expects($this->once())->method('decorate')->willReturnSelf();
        $this->commandBuilder->expects($this->once())->method('diffAlgorithm')->with($rule->getRuleOptions()?->getDiffAlgorithm())->willReturnSelf();
        $this->commandBuilder->expects($this->once())->method('format')->willReturnSelf();
        $this->commandBuilder->expects($this->once())->method('ignoreCrAtEol')->willReturnSelf();
        $this->commandBuilder->expects($this->once())->method('since')->with($startDate)->willReturnSelf();
        $this->commandBuilder->expects($this->once())->method('until')->with($endDate)->willReturnSelf();

        $this->commandBuilder->expects(static::never())->method('noMerges')->willReturnSelf();
        $this->commandBuilder->expects(static::never())->method('ignoreSpaceAtEol')->willReturnSelf();
        $this->commandBuilder->expects(static::never())->method('ignoreSpaceChange')->willReturnSelf();
        $this->commandBuilder->expects(static::never())->method('ignoreAllSpace')->willReturnSelf();
        $this->commandBuilder->expects(static::never())->method('ignoreBlankLines')->willReturnSelf();

        $this->factory->fromRule($config);
    }

    public function testFromRuleWithAllOptions(): void
    {
        $rule = new Rule();
        $rule->setRuleOptions(
            (new RuleOptions())
                ->setFrequency(Frequency::ONCE_PER_DAY)
                ->setIgnoreAllSpace(true)
                ->setIgnoreSpaceChange(true)
                ->setIgnoreBlankLines(true)
        );
        $startDate = new DateTimeImmutable('2021-10-18 21:05:00');
        $endDate   = new DateTimeImmutable('2021-10-18 22:05:00');
        $period    = new DatePeriod($startDate, new DateInterval('PT1H'), $endDate);
        $config    = new RuleConfiguration($period, $rule);

        $this->commandBuilder->expects($this->once())->method('remotes')->willReturnSelf();
        $this->commandBuilder->expects($this->once())->method('topoOrder')->willReturnSelf();
        $this->commandBuilder->expects($this->once())->method('patch')->willReturnSelf();
        $this->commandBuilder->expects($this->once())->method('decorate')->willReturnSelf();
        $this->commandBuilder->expects($this->once())->method('diffAlgorithm')->willReturnSelf();
        $this->commandBuilder->expects($this->once())->method('format')->willReturnSelf();
        $this->commandBuilder->expects($this->once())->method('ignoreCrAtEol')->willReturnSelf();
        $this->commandBuilder->expects($this->once())->method('since')->willReturnSelf();
        $this->commandBuilder->expects($this->once())->method('until')->willReturnSelf();

        $this->commandBuilder->expects($this->once())->method('noMerges')->willReturnSelf();
        $this->commandBuilder->expects($this->once())->method('ignoreSpaceAtEol')->willReturnSelf();
        $this->commandBuilder->expects($this->once())->method('ignoreSpaceChange')->willReturnSelf();
        $this->commandBuilder->expects($this->once())->method('ignoreAllSpace')->willReturnSelf();
        $this->commandBuilder->expects($this->once())->method('ignoreBlankLines')->willReturnSelf();

        $this->factory->fromRule($config);
    }
}

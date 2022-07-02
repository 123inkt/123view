<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\Log;

use DateTimeImmutable;
use DR\GitCommitNotification\Entity\Config\Frequency;
use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Entity\Config\RuleConfiguration;
use DR\GitCommitNotification\Entity\Config\RuleOptions;
use DR\GitCommitNotification\Service\Git\Log\FormatPatternFactory;
use DR\GitCommitNotification\Service\Git\Log\GitLogCommandBuilder;
use DR\GitCommitNotification\Service\Git\Log\GitLogCommandFactory;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\Log\GitLogCommandFactory
 * @covers ::__construct
 */
class GitLogCommandFactoryTest extends AbstractTestCase
{
    /** @var GitLogCommandBuilder&MockObject */
    private GitLogCommandBuilder $commandBuilder;
    private GitLogCommandFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commandBuilder = $this->createMock(GitLogCommandBuilder::class);
        $this->factory        = new GitLogCommandFactory($this->commandBuilder, new FormatPatternFactory());
    }

    /**
     * @covers ::fromRule
     */
    public function testFromRuleWithMinimalOptions(): void
    {
        $rule = new Rule();
        $rule->setRuleOptions(
            (new RuleOptions())
                ->setFrequency(Frequency::ONCE_PER_DAY)
                ->setIgnoreSpaceAtEol(false)
                ->setExcludeMergeCommits(false)
        );
        $config = new RuleConfiguration(new DateTimeImmutable('2021-10-18 21:05:00'), new DateTimeImmutable('2021-10-18 22:05:00'), [], $rule);

        $this->commandBuilder->expects(static::once())->method('start')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('remotes')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('topoOrder')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('patch')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('decorate')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('diffAlgorithm')->with($rule->getRuleOptions()?->getDiffAlgorithm())->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('format')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('ignoreCrAtEol')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('since')->with($config->startTime)->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('until')->with($config->endTime)->willReturnSelf();

        $this->commandBuilder->expects(static::never())->method('noMerges')->willReturnSelf();
        $this->commandBuilder->expects(static::never())->method('ignoreSpaceAtEol')->willReturnSelf();
        $this->commandBuilder->expects(static::never())->method('ignoreSpaceChange')->willReturnSelf();
        $this->commandBuilder->expects(static::never())->method('ignoreAllSpace')->willReturnSelf();
        $this->commandBuilder->expects(static::never())->method('ignoreBlankLines')->willReturnSelf();

        $this->factory->fromRule($config);
    }

    /**
     * @covers ::fromRule
     */
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
        $config = new RuleConfiguration(new DateTimeImmutable('2021-10-18 21:05:00'), new DateTimeImmutable('2021-10-18 22:05:00'), [], $rule);

        $this->commandBuilder->expects(static::once())->method('start')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('remotes')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('topoOrder')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('patch')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('decorate')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('diffAlgorithm')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('format')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('ignoreCrAtEol')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('since')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('until')->willReturnSelf();

        $this->commandBuilder->expects(static::once())->method('noMerges')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('ignoreSpaceAtEol')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('ignoreSpaceChange')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('ignoreAllSpace')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('ignoreBlankLines')->willReturnSelf();

        $this->factory->fromRule($config);
    }
}

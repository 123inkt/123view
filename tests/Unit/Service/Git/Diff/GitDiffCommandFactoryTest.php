<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\Diff;

use DR\GitCommitNotification\Entity\Notification\Rule;
use DR\GitCommitNotification\Entity\Notification\RuleOptions;
use DR\GitCommitNotification\Service\Git\Diff\GitDiffCommandBuilder;
use DR\GitCommitNotification\Service\Git\Diff\GitDiffCommandFactory;
use DR\GitCommitNotification\Service\Git\GitCommandBuilderFactory;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\Diff\GitDiffCommandFactory
 * @covers ::__construct
 */
class GitDiffCommandFactoryTest extends AbstractTestCase
{
    private GitDiffCommandBuilder&MockObject $commandBuilder;
    private GitDiffCommandFactory            $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commandBuilder = $this->createMock(GitDiffCommandBuilder::class);
        $factory              = $this->createMock(GitCommandBuilderFactory::class);
        $factory->method('createDiff')->willReturn($this->commandBuilder);
        $this->factory = new GitDiffCommandFactory($factory);
    }

    /**
     * @covers ::diffHashes
     */
    public function testDiffHashesMinimalOptions(): void
    {
        $rule = new Rule();
        $rule->setRuleOptions(
            (new RuleOptions())
                ->setIgnoreSpaceAtEol(false)
                ->setExcludeMergeCommits(false)
        );

        $this->commandBuilder->expects(static::once())->method('hashes')->with('startHash', 'endHash')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('diffAlgorithm')->with($rule->getRuleOptions()?->getDiffAlgorithm())->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('ignoreCrAtEol')->willReturnSelf();

        $this->commandBuilder->expects(static::never())->method('ignoreSpaceAtEol')->willReturnSelf();
        $this->commandBuilder->expects(static::never())->method('ignoreSpaceChange')->willReturnSelf();
        $this->commandBuilder->expects(static::never())->method('ignoreAllSpace')->willReturnSelf();
        $this->commandBuilder->expects(static::never())->method('ignoreBlankLines')->willReturnSelf();

        $this->factory->diffHashes($rule, 'startHash', 'endHash');
    }

    /**
     * @covers ::diffHashes
     */
    public function testFromRuleWithAllOptions(): void
    {
        $rule = new Rule();
        $rule->setRuleOptions(
            (new RuleOptions())
                ->setIgnoreAllSpace(true)
                ->setIgnoreSpaceChange(true)
                ->setIgnoreBlankLines(true)
        );

        $this->commandBuilder->expects(static::once())->method('hashes')->with('startHash', 'endHash')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('diffAlgorithm')->with($rule->getRuleOptions()?->getDiffAlgorithm())->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('ignoreCrAtEol')->willReturnSelf();

        $this->commandBuilder->expects(static::once())->method('ignoreSpaceAtEol')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('ignoreSpaceChange')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('ignoreAllSpace')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('ignoreBlankLines')->willReturnSelf();

        $this->factory->diffHashes($rule, 'startHash', 'endHash');
    }
}

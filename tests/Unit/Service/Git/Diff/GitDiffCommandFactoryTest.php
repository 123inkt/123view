<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Diff;

use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Notification\RuleOptions;
use DR\Review\Service\Git\Diff\GitDiffCommandBuilder;
use DR\Review\Service\Git\Diff\GitDiffCommandFactory;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(GitDiffCommandFactory::class)]
class GitDiffCommandFactoryTest extends AbstractTestCase
{
    private GitDiffCommandBuilder&MockObject $commandBuilder;
    private GitDiffCommandFactory            $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commandBuilder = $this->createMock(GitDiffCommandBuilder::class);
        $factory              = static::createStub(GitCommandBuilderFactory::class);
        $factory->method('createDiff')->willReturn($this->commandBuilder);
        $this->factory = new GitDiffCommandFactory($factory);
    }

    public function testDiffHashesMinimalOptions(): void
    {
        $rule = new Rule();
        $rule->setRuleOptions(
            (new RuleOptions())
                ->setIgnoreSpaceAtEol(false)
                ->setExcludeMergeCommits(false)
        );

        $this->commandBuilder->expects($this->once())->method('hashes')->with('startHash', 'endHash')->willReturnSelf();
        $this->commandBuilder->expects($this->once())->method('diffAlgorithm')->with($rule->getRuleOptions()?->getDiffAlgorithm())->willReturnSelf();
        $this->commandBuilder->expects($this->once())->method('ignoreCrAtEol')->willReturnSelf();

        $this->commandBuilder->expects($this->never())->method('ignoreSpaceAtEol')->willReturnSelf();
        $this->commandBuilder->expects($this->never())->method('ignoreSpaceChange')->willReturnSelf();
        $this->commandBuilder->expects($this->never())->method('ignoreAllSpace')->willReturnSelf();
        $this->commandBuilder->expects($this->never())->method('ignoreBlankLines')->willReturnSelf();

        $this->factory->diffHashes($rule, 'startHash', 'endHash');
    }

    public function testFromRuleWithAllOptions(): void
    {
        $rule = new Rule();
        $rule->setRuleOptions(
            (new RuleOptions())
                ->setIgnoreAllSpace(true)
                ->setIgnoreSpaceChange(true)
                ->setIgnoreBlankLines(true)
        );

        $this->commandBuilder->expects($this->once())->method('hashes')->with('startHash', 'endHash')->willReturnSelf();
        $this->commandBuilder->expects($this->once())->method('diffAlgorithm')->with($rule->getRuleOptions()?->getDiffAlgorithm())->willReturnSelf();
        $this->commandBuilder->expects($this->once())->method('ignoreCrAtEol')->willReturnSelf();

        $this->commandBuilder->expects($this->once())->method('ignoreSpaceAtEol')->willReturnSelf();
        $this->commandBuilder->expects($this->once())->method('ignoreSpaceChange')->willReturnSelf();
        $this->commandBuilder->expects($this->once())->method('ignoreAllSpace')->willReturnSelf();
        $this->commandBuilder->expects($this->once())->method('ignoreBlankLines')->willReturnSelf();

        $this->factory->diffHashes($rule, 'startHash', 'endHash');
    }
}

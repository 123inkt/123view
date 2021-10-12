<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\Diff;

use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Service\Git\Diff\GitDiffCommandBuilder;
use DR\GitCommitNotification\Service\Git\Diff\GitDiffCommandFactory;
use DR\GitCommitNotification\Tests\AbstractTest;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\Diff\GitDiffCommandFactory
 * @covers ::__construct
 */
class GitDiffCommandFactoryTest extends AbstractTest
{
    /** @var GitDiffCommandBuilder|MockObject */
    private GitDiffCommandBuilder $commandBuilder;
    private GitDiffCommandFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commandBuilder = $this->createMock(GitDiffCommandBuilder::class);
        $this->factory        = new GitDiffCommandFactory($this->commandBuilder);
    }

    /**
     * @covers ::diffHashes
     */
    public function testDiffHashesMinimalOptions(): void
    {
        $rule                      = new Rule();
        $rule->ignoreSpaceAtEol    = false;
        $rule->excludeMergeCommits = false;

        $this->commandBuilder->expects(static::once())->method('hashes')->with('startHash', 'endHash')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('diffAlgorithm')->with($rule->diffAlgorithm)->willReturnSelf();
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
        $rule                    = new Rule();
        $rule->ignoreAllSpace    = true;
        $rule->ignoreSpaceChange = true;
        $rule->ignoreBlankLines  = true;

        $this->commandBuilder->expects(static::once())->method('hashes')->with('startHash', 'endHash')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('diffAlgorithm')->with($rule->diffAlgorithm)->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('ignoreCrAtEol')->willReturnSelf();

        $this->commandBuilder->expects(static::once())->method('ignoreSpaceAtEol')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('ignoreSpaceChange')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('ignoreAllSpace')->willReturnSelf();
        $this->commandBuilder->expects(static::once())->method('ignoreBlankLines')->willReturnSelf();

        $this->factory->diffHashes($rule, 'startHash', 'endHash');
    }
}

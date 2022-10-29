<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\CherryPick;

use DR\GitCommitNotification\Service\Git\CherryPick\GitCherryPickCommandBuilder;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\CherryPick\GitCherryPickCommandBuilder
 * @covers ::__construct
 */
class GitCherryPickCommandBuilderTest extends AbstractTestCase
{
    private const DEFAULTS = ['git', 'cherry-pick'];

    private GitCherryPickCommandBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new GitCherryPickCommandBuilder('git');
    }

    /**
     * @covers ::build
     */
    public function testBuildDefaults(): void
    {
        static::assertSame(self::DEFAULTS, $this->builder->build());
    }

    /**
     * @covers ::setPath
     * @covers ::build
     */
    public function testSetPath(): void
    {
        static::assertSame(
            ['git', 'cherry-pick', '--strategy=strategy', '--abort', '--no-commit', 'hashes', '-X theirs'],
            $this->builder->strategy('strategy')->abort()->noCommit()->hashes(['hashes'])->conflictResolution('theirs')->build()
        );
    }

    /**
     * @covers ::command
     */
    public function testCommand(): void
    {
        static::assertSame('cherry-pick', $this->builder->command());
    }

    /**
     * @covers ::__toString
     */
    public function testToString(): void
    {
        static::assertSame(
            'git cherry-pick --strategy=strategy --abort --no-commit hashes -X theirs',
            (string)$this->builder->strategy('strategy')->abort()->noCommit()->hashes(['hashes'])->conflictResolution('theirs')
        );
    }
}

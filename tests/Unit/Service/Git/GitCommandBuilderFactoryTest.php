<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git;

use DR\GitCommitNotification\Service\Git\GitCommandBuilderFactory;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\GitCommandBuilderFactory
 * @covers ::__construct
 */
class GitCommandBuilderFactoryTest extends AbstractTestCase
{
    private GitCommandBuilderFactory $factory;

    public function setUp(): void
    {
        parent::setUp();
        $this->factory = new GitCommandBuilderFactory('git');
    }

    /**
     * @covers ::createCheryPick
     * @covers ::createCheckout
     * @covers ::createLog
     * @covers ::createAdd
     * @covers ::createBranch
     * @covers ::createShow
     * @covers ::createDiff
     * @covers ::createDiffTree
     * @covers ::createReset
     */
    public function testCreate(): void
    {
        $this->factory->createCheryPick();
        $this->factory->createCheckout();
        $this->factory->createLog();
        $this->factory->createAdd();
        $this->factory->createDiff();
        $this->factory->createDiffTree();
        $this->factory->createBranch();
        $this->factory->createShow();
        $this->factory->createReset();
        $this->addToAssertionCount(1);
    }
}

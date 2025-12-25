<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git;

use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitCommandBuilderFactory::class)]
class GitCommandBuilderFactoryTest extends AbstractTestCase
{
    private GitCommandBuilderFactory $factory;

    public function setUp(): void
    {
        parent::setUp();
        $this->factory = new GitCommandBuilderFactory('git');
    }

    public function testCreate(): void
    {
        $this->factory->createAdd();
        $this->factory->createBranch();
        $this->factory->createCheckout();
        $this->factory->createCherryPick();
        $this->factory->createClean();
        $this->factory->createCommit();
        $this->factory->createDiff();
        $this->factory->createDiffTree();
        $this->factory->createFetch();
        $this->factory->createGarbageCollect();
        $this->factory->createLog();
        $this->factory->createRemote();
        $this->factory->createReset();
        $this->factory->createRevList();
        $this->factory->createShow();
        $this->factory->createStatus();
        $this->factory->createGrep();
        $this->factory->createLsTree();
        $this->addToAssertionCount(1);
    }
}

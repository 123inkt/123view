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
        $this->factory->createCherryPick();
        $this->factory->createCheckout();
        $this->factory->createLog();
        $this->factory->createFetch();
        $this->factory->createAdd();
        $this->factory->createDiff();
        $this->factory->createDiffTree();
        $this->factory->createBranch();
        $this->factory->createShow();
        $this->factory->createReset();
        $this->factory->createRevList();
        $this->factory->createCommit();
        $this->factory->createClean();
        $this->factory->createGarbageCollect();
        $this->addToAssertionCount(1);
    }
}

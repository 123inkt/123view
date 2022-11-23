<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Repository\Config;

use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Repository\Config\RuleRepository;
use DR\GitCommitNotification\Tests\AbstractRepositoryTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Repository\Config\RuleRepository
 * @covers ::__construct
 */
class RuleRepositoryTest extends AbstractRepositoryTestCase
{
    private RuleRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->getRepository(RuleRepository::class);
    }

    /**
     * @covers ::save
     */
    public function testAdd(): void
    {
        $rule = new Rule();

        $this->expectPersist($rule);
        $this->expectFlush();
        $this->repository->save($rule, true);
    }

    /**
     * @covers ::remove
     */
    public function testRemove(): void
    {
        $rule = new Rule();

        $this->expectRemove($rule);
        $this->expectFlush();
        $this->repository->remove($rule, true);
    }

    protected function getRepositoryEntityClassString(): string
    {
        return Rule::class;
    }
}

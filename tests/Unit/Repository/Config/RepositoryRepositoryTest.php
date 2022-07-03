<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Repository\Config;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Repository\Config\RepositoryRepository;
use DR\GitCommitNotification\Tests\AbstractRepositoryTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Repository\Config\RepositoryRepository
 * @covers ::__construct
 */
class RepositoryRepositoryTest extends AbstractRepositoryTestCase
{
    private RepositoryRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->getRepository(RepositoryRepository::class);
    }

    /**
     * @covers ::add
     */
    public function testAdd(): void
    {
        $repository = new Repository();

        $this->expectPersist($repository);
        $this->expectFlush();
        $this->repository->add($repository, true);
    }

    /**
     * @covers ::remove
     */
    public function testRemove(): void
    {
        $repository = new Repository();

        $this->expectRemove($repository);
        $this->expectFlush();
        $this->repository->remove($repository, true);
    }

    protected function getRepositoryEntityClassString(): string
    {
        return Repository::class;
    }
}

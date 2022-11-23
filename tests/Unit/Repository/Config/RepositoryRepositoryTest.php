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
     * @covers ::save
     */
    public function testAdd(): void
    {
        $repository = new Repository();

        $this->expectPersist($repository);
        $this->expectFlush();
        $this->repository->save($repository, true);
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

    /**
     * @covers ::findByUpdateRevisions
     */
    public function testFindByUpdateRevisions(): void
    {
        $repository = new Repository();

        $this->expectCreateQueryBuilder('r')
            ->where('r.active = 1')
            ->andWhere(
                'r.updateRevisionsTimestamp + r.updateRevisionsInterval < :currentTime' .
                ' OR ' .
                'r.updateRevisionsTimestamp IS NULL'
            )->setParameter('currentTime', static::callback(static fn($time) => time() - $time <= 10))
            ->getResult([$repository]);
        static::assertSame([$repository], $this->repository->findByUpdateRevisions());
    }

    protected function getRepositoryEntityClassString(): string
    {
        return Repository::class;
    }
}

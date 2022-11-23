<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Repository\Review;

use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;
use DR\GitCommitNotification\Tests\AbstractRepositoryTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Repository\Review\RevisionRepository
 * @covers ::__construct
 */
class RevisionRepositoryTest extends AbstractRepositoryTestCase
{
    private RevisionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->getRepository(RevisionRepository::class);
    }

    /**
     * @covers ::save
     */
    public function testSave(): void
    {
        $revision = new Revision();

        $this->expectPersist($revision);
        $this->expectFlush();
        $this->repository->save($revision, true);
    }

    /**
     * @covers ::remove
     */
    public function testRemove(): void
    {
        $revision = new Revision();

        $this->expectRemove($revision);
        $this->expectFlush();
        $this->repository->remove($revision, true);
    }

    /**
     * @covers ::flush
     */
    public function testFlush(): void
    {
        $this->expectFlush();
        $this->repository->flush();
    }

    /**
     * @covers ::getPaginatorForSearchQuery
     */
    public function testGetPaginatorForSearchQuery(): void
    {
        $this->expectCreateQueryBuilder('r')
            ->leftJoin('r.review', 'c')
            ->where('r.repository = :repositoryId')
            ->setParameter('repositoryId', 5)
            ->orderBy('r.createTimestamp', 'DESC')
            ->setFirstResult(450)
            ->setMaxResults(50);

        $this->repository->getPaginatorForSearchQuery(5, 10, '', false);
    }

    /**
     * @covers ::getPaginatorForSearchQuery
     */
    public function testGetPaginatorForSearchQueryWithSearchQuery(): void
    {
        $this->expectCreateQueryBuilder('r')
            ->leftJoin('r.review', 'c')
            ->where('r.repository = :repositoryId')
            ->andWhereConsecutive(
                ['r.title LIKE :searchQuery OR r.authorEmail LIKE :searchQuery OR r.authorName LIKE :searchQuery'],
                ['r.review IS NOT NULL']
            )
            ->setParameterConsecutive(
                ['repositoryId', 5],
                ['searchQuery', '%\%search%']
            )
            ->orderBy('r.createTimestamp', 'DESC')
            ->setFirstResult(450)
            ->setMaxResults(50);

        $this->repository->getPaginatorForSearchQuery(5, 10, '%search', true);
    }

    protected function getRepositoryEntityClassString(): string
    {
        return Revision::class;
    }
}

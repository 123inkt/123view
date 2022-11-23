<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Repository\Review;

use DR\GitCommitNotification\Entity\Review\FileSeenStatus;
use DR\GitCommitNotification\Repository\Review\FileSeenStatusRepository;
use DR\GitCommitNotification\Tests\AbstractRepositoryTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Repository\Review\FileSeenStatusRepository
 * @covers ::__construct
 */
class FileSeenStatusRepositoryTest extends AbstractRepositoryTestCase
{
    private FileSeenStatusRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->getRepository(FileSeenStatusRepository::class);
    }

    /**
     * @covers ::save
     */
    public function testSave(): void
    {
        $status = new FileSeenStatus();

        $this->expectWrapInTransaction();
        $this->expectPersist($status);
        $this->expectFlush();
        $this->repository->save($status, true);
    }

    /**
     * @covers ::remove
     */
    public function testRemove(): void
    {
        $status = new FileSeenStatus();

        $this->expectRemove($status);
        $this->expectFlush();
        $this->repository->remove($status, true);
    }

    protected function getRepositoryEntityClassString(): string
    {
        return FileSeenStatus::class;
    }
}

<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Repository\Review;

use DR\GitCommitNotification\Entity\Review\CommentReply;
use DR\GitCommitNotification\Repository\Review\CommentReplyRepository;
use DR\GitCommitNotification\Tests\AbstractRepositoryTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Repository\Review\CommentReplyRepository
 * @covers ::__construct
 */
class CommentReplyRepositoryTest extends AbstractRepositoryTestCase
{
    private CommentReplyRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->getRepository(CommentReplyRepository::class);
    }

    /**
     * @covers ::save
     */
    public function testSave(): void
    {
        $comment = new CommentReply();

        $this->expectPersist($comment);
        $this->expectFlush();
        $this->repository->save($comment, true);
    }

    /**
     * @covers ::remove
     */
    public function testRemove(): void
    {
        $comment = new CommentReply();

        $this->expectRemove($comment);
        $this->expectFlush();
        $this->repository->remove($comment, true);
    }

    protected function getRepositoryEntityClassString(): string
    {
        return CommentReply::class;
    }
}

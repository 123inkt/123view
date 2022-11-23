<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Repository\Review;

use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Repository\Review\CommentRepository;
use DR\GitCommitNotification\Tests\AbstractRepositoryTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Repository\Review\CommentRepository
 * @covers ::__construct
 */
class CommentRepositoryTest extends AbstractRepositoryTestCase
{
    private CommentRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->getRepository(CommentRepository::class);
    }

    /**
     * @covers ::save
     */
    public function testSave(): void
    {
        $comment = new Comment();

        $this->expectPersist($comment);
        $this->expectFlush();
        $this->repository->save($comment, true);
    }

    /**
     * @covers ::remove
     */
    public function testRemove(): void
    {
        $comment = new Comment();

        $this->expectRemove($comment);
        $this->expectFlush();
        $this->repository->remove($comment, true);
    }

    /**
     * @covers ::findByReview
     */
    public function testFindByReview(): void
    {
        $review = new CodeReview();
        $review->setId(123);
        $filePath = 'filePath';
        $comment  = new Comment();

        $this->expectCreateQueryBuilder('c')
            ->where('c.review = :reviewId')
            ->andWhere('c.filePath = :filePath')
            ->setParameterConsecutive(
                ['reviewId', 123],
                ['filePath', $filePath]
            )
            ->orderBy('c.id', 'ASC')
            ->getResult([$comment]);

        static::assertSame([$comment], $this->repository->findByReview($review, $filePath));
    }

    protected function getRepositoryEntityClassString(): string
    {
        return Comment::class;
    }
}

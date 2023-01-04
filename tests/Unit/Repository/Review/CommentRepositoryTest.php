<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Repository\Review;

use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Tests\AbstractRepositoryTestCase;
use DR\Review\Tests\DataFixtures\CommentFixtures;
use DR\Review\Utility\Assert;
use Exception;

/**
 * @coversDefaultClass \DR\Review\Repository\Review\CommentRepository
 * @covers ::__construct
 */
class CommentRepositoryTest extends AbstractRepositoryTestCase
{
    /**
     * @covers ::findByReview
     * @throws Exception
     */
    public function testFindByReview(): void
    {
        $commentRepository = static::getService(CommentRepository::class);
        $comment           = Assert::notNull($commentRepository->findOneBy(['message' => 'message']));
        $review            = Assert::notNull($comment->getReview());

        static::assertCount(1, $commentRepository->findByReview($review, ['filepath']));
        static::assertCount(0, $commentRepository->findByReview($review, ['foobar']));

        $review->setId(-123);
        static::assertCount(0, $commentRepository->findByReview($review, ['filepath']));
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [CommentFixtures::class];
    }
}

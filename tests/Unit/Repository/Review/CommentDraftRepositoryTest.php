<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Repository\Review;

use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Tests\AbstractRepositoryTestCase;
use DR\Review\Tests\DataFixtures\DraftCommentFixtures;
use DR\Utils\Assert;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentRepository::class)]
class CommentDraftRepositoryTest extends AbstractRepositoryTestCase
{
    public function testGetPaginatorForDraftsByUser(): void
    {
        $userRepository    = static::getService(UserRepository::class);
        $commentRepository = static::getService(CommentRepository::class);

        $user = Assert::notNull($userRepository->findOneBy(['email' => 'sherlock@example.com']));

        $paginator = $commentRepository->getDraftsByUser($user, 1, 30);

        // Only the draft comment is returned, not the final one
        static::assertCount(1, $paginator);
        $comments = iterator_to_array($paginator);
        static::assertSame('draft message', $comments[0]->getMessage());
    }

    public function testCountDraftsByUser(): void
    {
        $userRepository    = static::getService(UserRepository::class);
        $commentRepository = static::getService(CommentRepository::class);

        $user = Assert::notNull($userRepository->findOneBy(['email' => 'sherlock@example.com']));

        static::assertSame(1, $commentRepository->countDraftsByUser($user));
    }

    /**
     * @return list<class-string>
     */
    protected function getFixtures(): array
    {
        return [DraftCommentFixtures::class];
    }
}

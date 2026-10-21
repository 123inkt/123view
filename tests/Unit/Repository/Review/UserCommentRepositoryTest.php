<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Repository\Review;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Tests\AbstractRepositoryTestCase;
use DR\Review\Tests\DataFixtures\UserCommentFixtures;
use DR\Utils\Assert;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentRepository::class)]
class UserCommentRepositoryTest extends AbstractRepositoryTestCase
{
    public function testFindOrderedByCreateTimestamp(): void
    {
        $result = self::getService(CommentRepository::class)
            ->findCommentsAndRepliesByUser($this->getUser(), '', CommentRepository::ORDER_CREATE_TIMESTAMP, 1);

        static::assertSame(
            [
                UserCommentFixtures::OWN_REPLY,
                UserCommentFixtures::OWN_DRAFT_COMMENT,
                UserCommentFixtures::OWN_REPLY_ON_OWN_DRAFT,
                UserCommentFixtures::OWN_COMMENT,
            ],
            self::getMessages($result)
        );
        static::assertInstanceOf(CommentReply::class, $result[0]);
        static::assertInstanceOf(Comment::class, $result[1]);
        static::assertInstanceOf(CommentReply::class, $result[2]);
        static::assertInstanceOf(Comment::class, $result[3]);
    }

    public function testFindOrderedByUpdateTimestamp(): void
    {
        $result = self::getService(CommentRepository::class)
            ->findCommentsAndRepliesByUser($this->getUser(), '', CommentRepository::ORDER_UPDATE_TIMESTAMP, 1);

        static::assertSame(
            [
                UserCommentFixtures::OWN_REPLY_ON_OWN_DRAFT,
                UserCommentFixtures::OWN_COMMENT,
                UserCommentFixtures::OWN_REPLY,
                UserCommentFixtures::OWN_DRAFT_COMMENT,
            ],
            self::getMessages($result)
        );
    }

    public function testFindWithSearchQuery(): void
    {
        $repository = self::getService(CommentRepository::class);
        $user       = $this->getUser();

        static::assertSame(
            [UserCommentFixtures::OWN_REPLY, UserCommentFixtures::OWN_REPLY_ON_OWN_DRAFT],
            self::getMessages($repository->findCommentsAndRepliesByUser($user, 'reply', CommentRepository::ORDER_CREATE_TIMESTAMP, 1))
        );

        // like wildcards should be escaped
        static::assertSame(
            [UserCommentFixtures::OWN_DRAFT_COMMENT],
            self::getMessages($repository->findCommentsAndRepliesByUser($user, '%', CommentRepository::ORDER_CREATE_TIMESTAMP, 1))
        );
        static::assertSame([], $repository->findCommentsAndRepliesByUser($user, '_', CommentRepository::ORDER_CREATE_TIMESTAMP, 1));
    }

    public function testFindPaginated(): void
    {
        $repository = self::getService(CommentRepository::class);
        $user       = $this->getUser();

        static::assertSame(
            [UserCommentFixtures::OWN_REPLY, UserCommentFixtures::OWN_DRAFT_COMMENT],
            self::getMessages($repository->findCommentsAndRepliesByUser($user, '', CommentRepository::ORDER_CREATE_TIMESTAMP, 1, 2))
        );
        static::assertSame(
            [UserCommentFixtures::OWN_REPLY_ON_OWN_DRAFT, UserCommentFixtures::OWN_COMMENT],
            self::getMessages($repository->findCommentsAndRepliesByUser($user, '', CommentRepository::ORDER_CREATE_TIMESTAMP, 2, 2))
        );
        static::assertSame([], $repository->findCommentsAndRepliesByUser($user, '', CommentRepository::ORDER_CREATE_TIMESTAMP, 3, 2));
    }

    public function testCountCommentsAndRepliesByUser(): void
    {
        $repository = self::getService(CommentRepository::class);
        $user       = $this->getUser();

        static::assertSame(4, $repository->countCommentsAndRepliesByUser($user, ''));
        static::assertSame(2, $repository->countCommentsAndRepliesByUser($user, 'reply'));
        static::assertSame(1, $repository->countCommentsAndRepliesByUser($user, '%'));
        static::assertSame(0, $repository->countCommentsAndRepliesByUser($user, 'foobar'));
    }

    /**
     * @param list<Comment|CommentReply> $commentsOrReplies
     *
     * @return string[]
     */
    private static function getMessages(array $commentsOrReplies): array
    {
        return array_map(static fn(Comment|CommentReply $commentOrReply): string => $commentOrReply->getMessage(), $commentsOrReplies);
    }

    private function getUser(): User
    {
        return Assert::notNull(self::getService(UserRepository::class)->findOneBy(['email' => 'sherlock@example.com']));
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [UserCommentFixtures::class];
    }
}

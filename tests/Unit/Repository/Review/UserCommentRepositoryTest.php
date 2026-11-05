<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Repository\Review;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\Review\CommentStateEnum;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\UserCommentRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Tests\AbstractRepositoryTestCase;
use DR\Review\Tests\DataFixtures\Test\UserCommentFixtures;
use DR\Utils\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(UserCommentRepository::class)]
class UserCommentRepositoryTest extends AbstractRepositoryTestCase
{
    public function testFindOrderedByCreateTimestamp(): void
    {
        $result = self::getService(UserCommentRepository::class)
            ->findCommentsAndRepliesByUser($this->getUser(), '', UserCommentRepository::ORDER_CREATE_TIMESTAMP, 1);

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
        $result = self::getService(UserCommentRepository::class)
            ->findCommentsAndRepliesByUser($this->getUser(), '', UserCommentRepository::ORDER_UPDATE_TIMESTAMP, 1);

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
        $repository = self::getService(UserCommentRepository::class);
        $user       = $this->getUser();

        static::assertSame(
            [UserCommentFixtures::OWN_REPLY, UserCommentFixtures::OWN_REPLY_ON_OWN_DRAFT],
            self::getMessages($repository->findCommentsAndRepliesByUser($user, 'reply', UserCommentRepository::ORDER_CREATE_TIMESTAMP, 1))
        );

        // like wildcards should be escaped
        static::assertSame(
            [UserCommentFixtures::OWN_DRAFT_COMMENT],
            self::getMessages($repository->findCommentsAndRepliesByUser($user, '%', UserCommentRepository::ORDER_CREATE_TIMESTAMP, 1))
        );
        static::assertSame([], $repository->findCommentsAndRepliesByUser($user, '_', UserCommentRepository::ORDER_CREATE_TIMESTAMP, 1));
    }

    public function testFindPaginated(): void
    {
        $repository = self::getService(UserCommentRepository::class);
        $user       = $this->getUser();

        static::assertSame(
            [UserCommentFixtures::OWN_REPLY, UserCommentFixtures::OWN_DRAFT_COMMENT],
            self::getMessages($repository->findCommentsAndRepliesByUser($user, '', UserCommentRepository::ORDER_CREATE_TIMESTAMP, 1, 2))
        );
        static::assertSame(
            [UserCommentFixtures::OWN_REPLY_ON_OWN_DRAFT, UserCommentFixtures::OWN_COMMENT],
            self::getMessages($repository->findCommentsAndRepliesByUser($user, '', UserCommentRepository::ORDER_CREATE_TIMESTAMP, 2, 2))
        );
        static::assertSame([], $repository->findCommentsAndRepliesByUser($user, '', UserCommentRepository::ORDER_CREATE_TIMESTAMP, 3, 2));
    }

    public function testCountCommentsAndRepliesByUser(): void
    {
        $repository = self::getService(UserCommentRepository::class);
        $user       = $this->getUser();

        static::assertSame(4, $repository->countCommentsAndRepliesByUser($user, ''));
        static::assertSame(2, $repository->countCommentsAndRepliesByUser($user, 'reply'));
        static::assertSame(1, $repository->countCommentsAndRepliesByUser($user, '%'));
        static::assertSame(0, $repository->countCommentsAndRepliesByUser($user, 'foobar'));
    }

    /**
     * @param string[] $expected
     */
    #[DataProvider('statesProvider')]
    public function testFindAndCountWithState(?CommentStateEnum $state, array $expected): void
    {
        $this->resolveComments();
        $repository = self::getService(UserCommentRepository::class);
        $user       = $this->getUser();

        static::assertSame(
            $expected,
            self::getMessages($repository->findCommentsAndRepliesByUser($user, '', UserCommentRepository::ORDER_CREATE_TIMESTAMP, 1, 30, $state))
        );
        static::assertSame(count($expected), $repository->countCommentsAndRepliesByUser($user, '', $state));
    }

    /**
     * @return array<string, array{?CommentStateEnum, string[]}>
     */
    public static function statesProvider(): array
    {
        return [
            'any'        => [null, [
                UserCommentFixtures::OWN_REPLY,
                UserCommentFixtures::OWN_DRAFT_COMMENT,
                UserCommentFixtures::OWN_REPLY_ON_OWN_DRAFT,
                UserCommentFixtures::OWN_COMMENT,
            ]],
            'resolved'   => [CommentStateEnum::Resolved, [UserCommentFixtures::OWN_REPLY, UserCommentFixtures::OWN_COMMENT]],
            'unresolved' => [CommentStateEnum::Open, [UserCommentFixtures::OWN_DRAFT_COMMENT, UserCommentFixtures::OWN_REPLY_ON_OWN_DRAFT]],
        ];
    }

    public function testFindAndCountWithStateAndSearchQuery(): void
    {
        $this->resolveComments();
        $repository = self::getService(UserCommentRepository::class);
        $user       = $this->getUser();

        foreach ([CommentStateEnum::Resolved, CommentStateEnum::Open] as $state) {
            $result = $repository->findCommentsAndRepliesByUser($user, 'reply', UserCommentRepository::ORDER_CREATE_TIMESTAMP, 1, 30, $state);

            static::assertSame(
                [$state === CommentStateEnum::Resolved ? UserCommentFixtures::OWN_REPLY : UserCommentFixtures::OWN_REPLY_ON_OWN_DRAFT],
                self::getMessages($result)
            );
            static::assertSame(1, $repository->countCommentsAndRepliesByUser($user, 'reply', $state));
        }
    }

    public function testFindWithStateOrderedByUpdate(): void
    {
        $this->resolveComments();
        $result = self::getService(UserCommentRepository::class)
            ->findCommentsAndRepliesByUser($this->getUser(), '', UserCommentRepository::ORDER_UPDATE_TIMESTAMP, 1, 30, CommentStateEnum::Resolved);

        static::assertSame([UserCommentFixtures::OWN_COMMENT, UserCommentFixtures::OWN_REPLY], self::getMessages($result));
    }

    public function testFindWithStatePaginated(): void
    {
        $this->resolveComments();
        $repository = self::getService(UserCommentRepository::class);
        $user       = $this->getUser();

        $firstPage = $repository->findCommentsAndRepliesByUser(
            $user,
            '',
            UserCommentRepository::ORDER_CREATE_TIMESTAMP,
            1,
            1,
            CommentStateEnum::Open
        );
        $secondPage = $repository->findCommentsAndRepliesByUser(
            $user,
            '',
            UserCommentRepository::ORDER_CREATE_TIMESTAMP,
            2,
            1,
            CommentStateEnum::Open
        );
        $thirdPage = $repository->findCommentsAndRepliesByUser(
            $user,
            '',
            UserCommentRepository::ORDER_CREATE_TIMESTAMP,
            3,
            1,
            CommentStateEnum::Open
        );

        static::assertSame([UserCommentFixtures::OWN_DRAFT_COMMENT], self::getMessages($firstPage));
        static::assertSame([UserCommentFixtures::OWN_REPLY_ON_OWN_DRAFT], self::getMessages($secondPage));
        static::assertSame([], $thirdPage);
    }

    private function resolveComments(): void
    {
        $connection = Assert::notNull($this->entityManager)->getConnection();
        foreach ([UserCommentFixtures::OWN_COMMENT, UserCommentFixtures::OTHER_COMMENT, UserCommentFixtures::OTHER_DRAFT_COMMENT] as $message) {
            $connection->update('comment', ['state' => CommentStateEnum::Resolved->value], ['message' => $message]);
        }
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

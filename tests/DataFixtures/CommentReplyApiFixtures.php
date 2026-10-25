<?php

declare(strict_types=1);

namespace DR\Review\Tests\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CommentStateEnum;
use DR\Review\Entity\Review\CommentTagEnum;
use DR\Review\Entity\Review\CommentTypeEnum;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\User\User;
use DR\Review\Entity\User\UserAccessToken;
use DR\Utils\Assert;

class CommentReplyApiFixtures extends Fixture implements DependentFixtureInterface
{
    public const string FINAL_COMMENT_MESSAGE       = 'reply final parent';
    public const string OWN_DRAFT_COMMENT_MESSAGE   = 'reply own draft parent';
    public const string OTHER_DRAFT_COMMENT_MESSAGE = 'reply other draft parent';
    public const string FINAL_REPLY_ONE             = 'final reply one';
    public const string FINAL_REPLY_TWO             = 'final reply two';
    public const string OWN_DRAFT_REPLY             = 'own draft reply';
    public const string OTHER_DRAFT_REPLY           = 'other draft reply';
    public const string OTHER_USER_EMAIL             = 'reply-other@example.com';
    public const string OTHER_USER_TOKEN             = 'comment-reply-other-user-token';

    public function load(ObjectManager $manager): void
    {
        $author = Assert::notNull($manager->getRepository(User::class)->findOneBy(['email' => 'sherlock@example.com']));
        $review = Assert::notNull($manager->getRepository(CodeReview::class)->findOneBy(['title' => 'title']));
        $other  = new User()
            ->setName('John Watson')
            ->setEmail(self::OTHER_USER_EMAIL)
            ->addRole('ROLE_USER');
        $token = new UserAccessToken()
            ->setUser($other)
            ->setName('comment reply test token')
            ->setToken(str_pad(self::OTHER_USER_TOKEN, 80, '0'))
            ->setUsages(0)
            ->setCreateTimestamp(1_000)
            ->setUseTimestamp(1_000);

        $manager->persist($other);
        $manager->persist($token);
        $manager->flush();

        $connection = Assert::isInstanceOf($manager, EntityManagerInterface::class)->getConnection();
        $final      = $this->insertComment($connection, $author->getId(), $review->getId(), self::FINAL_COMMENT_MESSAGE, CommentTypeEnum::Final);
        $ownDraft   = $this->insertComment($connection, $author->getId(), $review->getId(), self::OWN_DRAFT_COMMENT_MESSAGE, CommentTypeEnum::Draft);
        $otherDraft = $this->insertComment($connection, $other->getId(), $review->getId(), self::OTHER_DRAFT_COMMENT_MESSAGE, CommentTypeEnum::Draft);

        $this->insertReply($connection, $final, $author->getId(), self::FINAL_REPLY_ONE, 1_000, CommentTagEnum::Suggestion);
        $this->insertReply($connection, $final, $other->getId(), self::FINAL_REPLY_TWO, 1_000, null);
        $this->insertReply($connection, $ownDraft, $other->getId(), self::OWN_DRAFT_REPLY, 2_000, null);
        $this->insertReply($connection, $otherDraft, $other->getId(), self::OTHER_DRAFT_REPLY, 3_000, null);
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [UserFixtures::class, CodeReviewFixtures::class, UserAccessTokenFixtures::class];
    }

    private function insertComment(Connection $connection, int $userId, int $reviewId, string $message, CommentTypeEnum $type): int
    {
        $connection->insert('comment', [
            'file_path'           => 'src/Foo.php',
            'line_reference'      => (string)new LineReference(null, 'src/Foo.php', 40, 2, 42, 'abc123'),
            'state'               => CommentStateEnum::Open->value,
            'ext_reference_id'    => null,
            'message'             => $message,
            'tag'                 => CommentTagEnum::Suggestion->value,
            'type'                => $type->value,
            'create_timestamp'    => 1_000,
            'update_timestamp'    => 2_000,
            'notification_status' => null,
            'review_id'           => $reviewId,
            'user_id'             => $userId,
        ]);

        return (int)$connection->lastInsertId();
    }

    private function insertReply(Connection $connection, int $commentId, int $userId, string $message, int $timestamp, ?CommentTagEnum $tag): void
    {
        $connection->insert('comment_reply', [
            'comment_id'          => $commentId,
            'user_id'             => $userId,
            'message'             => $message,
            'tag'                 => $tag?->value,
            'ext_reference_id'    => null,
            'create_timestamp'    => $timestamp,
            'update_timestamp'    => $timestamp,
            'notification_status' => null,
        ]);
    }
}

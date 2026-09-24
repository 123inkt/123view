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
use DR\Utils\Assert;

class DeleteCommentApiFixtures extends Fixture implements DependentFixtureInterface
{
    public const TARGET_COMMENT_MESSAGE    = 'delete author final';
    public const UNRELATED_COMMENT_MESSAGE = 'delete other final';

    public function load(ObjectManager $manager): void
    {
        $author = Assert::notNull($manager->getRepository(User::class)->findOneBy(['email' => 'sherlock@example.com']));
        $review = Assert::notNull($manager->getRepository(CodeReview::class)->findOneBy(['title' => 'title']));

        $other = (new User())
            ->setName('John Watson')
            ->setEmail('watson@example.com')
            ->addRole('ROLE_USER');
        $manager->persist($other);
        $manager->flush();

        $connection = Assert::isInstanceOf($manager, EntityManagerInterface::class)->getConnection();

        $targetCommentId    = $this->insertComment($connection, $author->getId(), $review->getId(), self::TARGET_COMMENT_MESSAGE);
        $unrelatedCommentId = $this->insertComment($connection, $other->getId(), $review->getId(), self::UNRELATED_COMMENT_MESSAGE);

        $this->insertReply($connection, $targetCommentId, $author->getId(), 'first target reply');
        $this->insertReply($connection, $targetCommentId, $author->getId(), 'second target reply');
        $this->insertReply($connection, $unrelatedCommentId, $other->getId(), 'unrelated reply');
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [UserFixtures::class, CodeReviewFixtures::class, UserAccessTokenFixtures::class];
    }

    private function insertComment(Connection $connection, int $userId, int $reviewId, string $message): int
    {
        $connection->insert('comment', [
            'file_path'           => 'src/Foo.php',
            'line_reference'      => (string)new LineReference(null, 'src/Foo.php', 40, 2, 42, 'abc123'),
            'state'               => CommentStateEnum::Open->value,
            'ext_reference_id'    => null,
            'message'             => $message,
            'tag'                 => CommentTagEnum::Suggestion->value,
            'type'                => CommentTypeEnum::Final->value,
            'create_timestamp'    => 1_000,
            'update_timestamp'    => 2_000,
            'notification_status' => null,
            'review_id'           => $reviewId,
            'user_id'             => $userId,
        ]);

        return (int)$connection->lastInsertId();
    }

    private function insertReply(Connection $connection, int $commentId, int $userId, string $message): void
    {
        $connection->insert('comment_reply', [
            'comment_id'          => $commentId,
            'user_id'             => $userId,
            'message'             => $message,
            'tag'                 => null,
            'ext_reference_id'    => null,
            'create_timestamp'    => 1_000,
            'update_timestamp'    => 2_000,
            'notification_status' => null,
        ]);
    }
}

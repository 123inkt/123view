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

class PatchCommentApiFixtures extends Fixture implements DependentFixtureInterface
{
    public const string OTHER_USER_EMAIL = 'watson@example.com';
    public const string OTHER_USER_TOKEN = 'patch-other-user-token';

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
            ->setName('patch test token')
            ->setToken(str_pad(self::OTHER_USER_TOKEN, 80, '0'))
            ->setUsages(0)
            ->setCreateTimestamp(1_000)
            ->setUseTimestamp(1_000);

        $manager->persist($other);
        $manager->persist($token);
        $manager->flush();

        $entityManager = Assert::isInstanceOf($manager, EntityManagerInterface::class);
        $connection    = $entityManager->getConnection();
        $this->insertComment($connection, $author->getId(), $review->getId(), 'patch author final', CommentTypeEnum::Final);
        $this->insertComment($connection, $author->getId(), $review->getId(), 'patch author draft', CommentTypeEnum::Draft);
        $this->insertComment($connection, $other->getId(), $review->getId(), 'patch other final', CommentTypeEnum::Final);
        $this->insertComment($connection, $other->getId(), $review->getId(), 'patch other draft', CommentTypeEnum::Draft);
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [CommentApiFixtures::class, UserAccessTokenFixtures::class];
    }

    private function insertComment(Connection $connection, int $userId, int $reviewId, string $message, CommentTypeEnum $type,): void
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
    }
}

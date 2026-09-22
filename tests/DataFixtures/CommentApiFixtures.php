<?php

declare(strict_types=1);

namespace DR\Review\Tests\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CommentTagEnum;
use DR\Review\Entity\Review\CommentTypeEnum;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\Review\LineReferenceStateEnum;
use DR\Review\Entity\User\User;
use DR\Utils\Assert;

class CommentApiFixtures extends Fixture implements DependentFixtureInterface
{
    public const OWN_FINAL = 'api own final';

    public function load(ObjectManager $manager): void
    {
        $author = Assert::notNull($manager->getRepository(User::class)->findOneBy(['email' => 'sherlock@example.com']));
        $review = Assert::notNull($manager->getRepository(CodeReview::class)->findOneBy(['title' => 'title']));

        $entityManager = Assert::isInstanceOf($manager, EntityManagerInterface::class);
        $connection    = $entityManager->getConnection();

        $connection->insert(
            'comment',
            [
                'file_path'           => 'src/Foo.php',
                'line_reference'      => (string)new LineReference(null, 'src/Foo.php', 40, 2, 42, 'abc123', LineReferenceStateEnum::Modified),
                'state'               => 'open',
                'ext_reference_id'    => null,
                'message'             => self::OWN_FINAL,
                'tag'                 => CommentTagEnum::Suggestion->value,
                'type'                => CommentTypeEnum::Final->value,
                'create_timestamp'    => 1000,
                'update_timestamp'    => 2000,
                'notification_status' => null,
                'review_id'           => $review->getId(),
                'user_id'             => $author->getId(),
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [UserFixtures::class, CodeReviewFixtures::class];
    }
}

<?php

declare(strict_types=1);

namespace DR\Review\Tests\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CommentTagEnum;
use DR\Review\Entity\Review\CommentTypeEnum;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\Review\LineReferenceStateEnum;
use DR\Review\Entity\User\User;
use DR\Review\Entity\User\UserSetting;
use DR\Review\Security\Role\Roles;
use DR\Utils\Assert;

class CommentApiFixtures extends Fixture implements DependentFixtureInterface
{
    public const OWN_FINAL     = 'api own final';
    public const FOREIGN_FINAL = 'api foreign final';
    public const OWN_DRAFT     = 'api own draft';
    public const FOREIGN_DRAFT = 'api foreign draft';
    public const LEGACY        = 'api legacy final';

    public function load(ObjectManager $manager): void
    {
        $author = Assert::notNull($manager->getRepository(User::class)->findOneBy(['email' => 'sherlock@example.com']));
        $viewer = new User()
            ->setName('John Watson')
            ->setEmail('watson@example.com')
            ->setSetting(new UserSetting())
            ->addRole(Roles::ROLE_USER);
        $manager->persist($viewer);
        $manager->flush();

        $review = Assert::notNull($manager->getRepository(CodeReview::class)->findOneBy(['title' => 'title']));

        $entityManager = Assert::isInstanceOf($manager, EntityManagerInterface::class);
        $connection    = $entityManager->getConnection();

        $this->insertComment(
            $connection,
            self::OWN_FINAL,
            $author,
            $review,
            new LineReference(null, 'src/Foo.php', 40, 2, 42, 'abc123', LineReferenceStateEnum::Modified),
            CommentTypeEnum::Final,
            CommentTagEnum::Suggestion,
            1000,
            2000,
        );
        $this->insertComment(
            $connection,
            self::FOREIGN_FINAL,
            $viewer,
            $review,
            new LineReference(null, 'src/Bar.php', 10, 0, 10, 'def456', LineReferenceStateEnum::Added),
            CommentTypeEnum::Final,
            CommentTagEnum::ChangeRequest,
            1001,
            2001,
        );
        $this->insertComment(
            $connection,
            self::OWN_DRAFT,
            $author,
            $review,
            new LineReference(null, 'src/Draft.php', 20, 0, 20),
            CommentTypeEnum::Draft,
            null,
            1002,
            2002,
        );
        $this->insertComment(
            $connection,
            self::FOREIGN_DRAFT,
            $viewer,
            $review,
            new LineReference(null, 'src/ForeignDraft.php', 30, 0, 30),
            CommentTypeEnum::Draft,
            null,
            1003,
            2003,
        );
        $this->insertComment(
            $connection,
            self::LEGACY,
            $author,
            $review,
            new LineReference(null, 'src/Legacy.php', 7, 1, 8),
            CommentTypeEnum::Final,
            null,
            1004,
            2004,
        );
    }

    /**
     * @return list<class-string>
     */
    public function getDependencies(): array
    {
        return [UserFixtures::class, CodeReviewFixtures::class];
    }

    private function insertComment(
        Connection $connection,
        string $message,
        User $user,
        CodeReview $review,
        LineReference $lineReference,
        CommentTypeEnum $type,
        ?CommentTagEnum $tag,
        int $createTimestamp,
        int $updateTimestamp,
    ): void {
        $connection->insert('comment', [
            'file_path'          => $lineReference->newPath ?? '',
            'line_reference'     => (string)$lineReference,
            'state'              => 'open',
            'ext_reference_id'   => null,
            'message'            => $message,
            'tag'                => $tag?->value,
            'type'               => $type->value,
            'create_timestamp'   => $createTimestamp,
            'update_timestamp'   => $updateTimestamp,
            'notification_status' => null,
            'review_id'          => $review->getId(),
            'user_id'            => $user->getId(),
        ]);
    }
}

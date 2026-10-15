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
use DR\Review\Entity\Review\CommentTypeEnum;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\User\User;
use DR\Utils\Assert;

/**
 * Comments and replies of sherlock@example.com, mixed with comments and replies of another user.
 */
class UserCommentFixtures extends Fixture implements DependentFixtureInterface
{
    public const string OWN_COMMENT              = 'my final comment';
    public const string OWN_DRAFT_COMMENT        = 'my draft comment 100%';
    public const string OWN_REPLY                = 'my reply';
    public const string OWN_REPLY_ON_OWN_DRAFT   = 'my reply on my draft';
    public const string OWN_REPLY_ON_OTHER_DRAFT = 'my reply on other draft';
    public const string OTHER_COMMENT            = 'other final comment';
    public const string OTHER_DRAFT_COMMENT      = 'other draft comment';
    public const string OTHER_REPLY              = 'other reply';

    public function load(ObjectManager $manager): void
    {
        $user   = Assert::notNull($manager->getRepository(User::class)->findOneBy(['email' => 'sherlock@example.com']));
        $review = Assert::notNull($manager->getRepository(CodeReview::class)->findOneBy(['title' => 'title']));
        $other  = new User()
            ->setName('John Watson')
            ->setEmail('user-comment-other@example.com')
            ->addRole('ROLE_USER');
        $manager->persist($other);
        $manager->flush();

        $connection   = Assert::isInstanceOf($manager, EntityManagerInterface::class)->getConnection();
        $comment      = $this->insertComment($connection, $review, $user, self::OWN_COMMENT, CommentTypeEnum::Final, 1000, 5000);
        $draft        = $this->insertComment($connection, $review, $user, self::OWN_DRAFT_COMMENT, CommentTypeEnum::Draft, 3000, 3000);
        $otherComment = $this->insertComment($connection, $review, $other, self::OTHER_COMMENT, CommentTypeEnum::Final, 2000, 2000);
        $otherDraft   = $this->insertComment($connection, $review, $other, self::OTHER_DRAFT_COMMENT, CommentTypeEnum::Draft, 2500, 2500);

        $this->insertReply($connection, $otherComment, $user, self::OWN_REPLY, 4000, 4000);
        $this->insertReply($connection, $draft, $user, self::OWN_REPLY_ON_OWN_DRAFT, 2000, 7000);
        $this->insertReply($connection, $otherDraft, $user, self::OWN_REPLY_ON_OTHER_DRAFT, 6000, 6000);
        $this->insertReply($connection, $comment, $other, self::OTHER_REPLY, 4500, 4500);
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [UserFixtures::class, CodeReviewFixtures::class];
    }

    private function insertComment(
        Connection $connection,
        CodeReview $review,
        User $user,
        string $message,
        CommentTypeEnum $type,
        int $createTimestamp,
        int $updateTimestamp
    ): int {
        $connection->insert('comment', [
            'file_path'           => 'src/Foo.php',
            'line_reference'      => (string)new LineReference(null, 'src/Foo.php', 40, 2, 42, 'abc123'),
            'state'               => CommentStateEnum::Open->value,
            'ext_reference_id'    => null,
            'message'             => $message,
            'tag'                 => null,
            'type'                => $type->value,
            'create_timestamp'    => $createTimestamp,
            'update_timestamp'    => $updateTimestamp,
            'notification_status' => null,
            'review_id'           => $review->getId(),
            'user_id'             => $user->getId(),
        ]);

        return (int)$connection->lastInsertId();
    }

    private function insertReply(
        Connection $connection,
        int $commentId,
        User $user,
        string $message,
        int $createTimestamp,
        int $updateTimestamp
    ): void {
        $connection->insert('comment_reply', [
            'comment_id'          => $commentId,
            'user_id'             => $user->getId(),
            'message'             => $message,
            'tag'                 => null,
            'ext_reference_id'    => null,
            'create_timestamp'    => $createTimestamp,
            'update_timestamp'    => $updateTimestamp,
            'notification_status' => null,
        ]);
    }
}

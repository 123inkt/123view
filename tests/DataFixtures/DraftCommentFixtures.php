<?php
declare(strict_types=1);

namespace DR\Review\Tests\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentTypeEnum;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\User\User;
use DR\Utils\Assert;

class DraftCommentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $user   = Assert::notNull($manager->getRepository(User::class)->findOneBy(['email' => 'sherlock@example.com']));
        $review = Assert::notNull($manager->getRepository(CodeReview::class)->findOneBy(['title' => 'title']));

        $draft = new Comment();
        $draft->setLineReference(new LineReference());
        $draft->setMessage('draft message');
        $draft->setFilePath('src/Foo.php');
        $draft->setType(CommentTypeEnum::Draft);
        $draft->setCreateTimestamp(12345678);
        $draft->setUpdateTimestamp(12345678);
        $draft->setReview($review);
        $draft->setUser($user);
        $manager->persist($draft);

        $final = new Comment();
        $final->setLineReference(new LineReference());
        $final->setMessage('final message');
        $final->setFilePath('src/Bar.php');
        $final->setType(CommentTypeEnum::Final);
        $final->setCreateTimestamp(12345679);
        $final->setUpdateTimestamp(12345679);
        $final->setReview($review);
        $final->setUser($user);
        $manager->persist($final);

        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [UserFixtures::class, CodeReviewFixtures::class];
    }
}

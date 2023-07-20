<?php
declare(strict_types=1);

namespace DR\Review\Tests\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\User\User;
use DR\Utils\Assert;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $user   = $manager->getRepository(User::class)->findOneBy(['email' => 'sherlock@example.com']);
        $review = $manager->getRepository(CodeReview::class)->findOneBy(['title' => 'title']);

        $comment = new Comment();
        $comment->setLineReference(new LineReference());
        $comment->setMessage('message');
        $comment->setFilePath('filepath');
        $comment->setCreateTimestamp(12345678);
        $comment->setUpdateTimestamp(87654321);
        $comment->setReview(Assert::notNull($review));
        $comment->setUser(Assert::notNull($user));

        $manager->persist($comment);
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

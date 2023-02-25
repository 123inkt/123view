<?php
declare(strict_types=1);

namespace DR\Review\Tests\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Review\Entity\User\User;
use DR\Review\Utility\Assert;

class CodeReviewActivityFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $review = Assert::notNull($manager->getRepository(CodeReview::class)->findOneBy(['projectId' => CodeReviewFixtures::PROJECT_ID]));
        $user   = Assert::notNull($manager->getRepository(User::class)->findOneBy(['name' => 'Sherlock Holmes']));

        $activity = new CodeReviewActivity();
        $activity->setUser($user);
        $activity->setReview($review);
        $activity->setEventName('event');
        $activity->setData(['foo' => 'bar']);
        $activity->setCreateTimestamp(12346789);

        $manager->persist($activity);
        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [CodeReviewFixtures::class, UserFixtures::class];
    }
}

<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Utility\Assert;

class CodeReviewFixtures extends Fixture implements DependentFixtureInterface
{
    public const REVIEW_ID = 1504;

    public function load(ObjectManager $manager): void
    {
        $repository = Assert::notNull($manager->getRepository(Repository::class)->findOneBy(['name' => 'repository']));

        $review = new CodeReview();
        $review->setId(self::REVIEW_ID);
        $review->setProjectId(5);
        $review->setTitle('title');
        $review->setReferenceId('reference');
        $review->setDescription('description');
        $review->setCreateTimestamp(12346789);
        $review->setRepository($repository);
        $manager->persist($review);
        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [RepositoryFixtures::class];
    }
}

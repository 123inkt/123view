<?php
declare(strict_types=1);

namespace DR\Review\Tests\DataFixtures\Dev;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\User\User;

class CodeReviewFixtures extends Fixture implements DependentFixtureInterface
{
    public const string REFERENCE = 'pull-request-1680-review';

    public function load(ObjectManager $manager): void
    {
        $repository = $this->getReference(RepositoryFixtures::REFERENCE, Repository::class);
        $developer  = $this->getReference(UserFixtures::DEVELOPER_REFERENCE, User::class);
        $renovate   = $this->getReference(UserFixtures::RENOVATE_REFERENCE, User::class);
        $now        = time();

        $review = new CodeReview();
        $review->setProjectId(1);
        $review->setTitle('Update dependency phpstan/phpstan-strict-rules to v2.1.1');
        $review->setDescription('Development fixture based on closed GitHub PR #1680.');
        $review->setType(CodeReviewType::COMMITS);
        $review->setState(CodeReviewStateType::OPEN);
        $review->setActors([$developer->getId(), $renovate->getId()]);
        $review->setCreateTimestamp($now - 180);
        $review->setUpdateTimestamp($now - 60);
        $review->setRepository($repository);

        $manager->persist($review);
        $manager->flush();

        $this->addReference(self::REFERENCE, $review);
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [RepositoryFixtures::class, UserFixtures::class];
    }
}

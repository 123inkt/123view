<?php
declare(strict_types=1);

namespace DR\Review\Tests\DataFixtures\dev;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\User\User;

class CodeReviewerFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $review   = $this->getReference(CodeReviewFixtures::REFERENCE, CodeReview::class);
        $renovate = $this->getReference(UserFixtures::RENOVATE_REFERENCE, User::class);

        $reviewer = new CodeReviewer();
        $reviewer->setReview($review);
        $reviewer->setUser($renovate);
        $reviewer->setState(CodeReviewerStateType::ACCEPTED);
        $reviewer->setStateTimestamp(time() - 60);

        $manager->persist($reviewer);
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

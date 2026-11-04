<?php
declare(strict_types=1);

namespace DR\Review\Tests\DataFixtures\Dev;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Review\Entity\User\User;
use DR\Review\Message\Review\ReviewAccepted;
use DR\Review\Message\Review\ReviewOpened;

class CodeReviewActivityFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $review   = $this->getReference(CodeReviewFixtures::REFERENCE, CodeReview::class);
        $renovate = $this->getReference(UserFixtures::RENOVATE_REFERENCE, User::class);
        $now      = time();

        $opened = new CodeReviewActivity();
        $opened->setReview($review);
        $opened->setEventName(ReviewOpened::NAME);
        $opened->setData(['reviewId' => $review->getId(), 'userId' => null]);
        $opened->setCreateTimestamp($now - 120);

        $accepted = new CodeReviewActivity();
        $accepted->setReview($review);
        $accepted->setUser($renovate);
        $accepted->setEventName(ReviewAccepted::NAME);
        $accepted->setData(['reviewId' => $review->getId(), 'userId' => $renovate->getId()]);
        $accepted->setCreateTimestamp($now - 60);

        $manager->persist($opened);
        $manager->persist($accepted);
        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [CodeReviewFixtures::class, CodeReviewerFixtures::class, UserFixtures::class];
    }
}

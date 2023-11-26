<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Repository\Review;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Review\Repository\Review\CodeReviewActivityRepository;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Tests\AbstractRepositoryTestCase;
use DR\Review\Tests\DataFixtures\CodeReviewFixtures;
use DR\Utils\Assert;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeReviewActivityRepository::class)]
class CodeReviewActivityRepositoryTest extends AbstractRepositoryTestCase
{
    private CodeReviewActivityRepository $activityRepository;
    private CodeReviewRepository         $reviewRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->activityRepository = static::getService(CodeReviewActivityRepository::class);
        $this->reviewRepository   = static::getService(CodeReviewRepository::class);
    }

    public function testFindForUser(): void
    {
        $actorId = 456;
        $review  = Assert::notNull($this->reviewRepository->findOneBy(['title' => 'title']));
        $review->setActors([$actorId]);
        $this->reviewRepository->save($review, true);

        $activity = new CodeReviewActivity();
        $activity->setEventName('event');
        $activity->setReview($review);
        $activity->setCreateTimestamp(time());
        $this->activityRepository->save($activity, true);

        $result = $this->activityRepository->findForUser(456, ['event']);
        static::assertCount(1, $result);
    }

    public function testFindForUserForRepository(): void
    {
        $actorId = 456;
        $review  = Assert::notNull($this->reviewRepository->findOneBy(['title' => 'title']));
        $review->setActors([$actorId]);
        $this->reviewRepository->save($review, true);

        $activity = new CodeReviewActivity();
        $activity->setEventName('event');
        $activity->setReview($review);
        $activity->setCreateTimestamp(time());
        $this->activityRepository->save($activity, true);

        // expect to find 1 result for this repository
        static::assertCount(1, $this->activityRepository->findForUser(456, ['event'], $review->getRepository()));

        // expect to find 0 result for zero repository
        static::assertCount(0, $this->activityRepository->findForUser(456, ['event'], (new Repository())->setId(0)));
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [CodeReviewFixtures::class];
    }
}

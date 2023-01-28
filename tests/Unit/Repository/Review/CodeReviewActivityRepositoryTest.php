<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Repository\Review;

use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Review\Repository\Review\CodeReviewActivityRepository;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Tests\AbstractRepositoryTestCase;
use DR\Review\Tests\DataFixtures\CodeReviewFixtures;
use DR\Review\Utility\Assert;

/**
 * @coversDefaultClass \DR\Review\Repository\Review\CodeReviewActivityRepository
 * @covers ::__construct
 */
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

    /**
     * @covers ::findForUser
     */
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

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [CodeReviewFixtures::class];
    }
}

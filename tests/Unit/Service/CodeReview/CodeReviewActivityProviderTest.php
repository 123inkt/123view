<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\User\User;
use DR\Review\Message\Review\ReviewCreated;
use DR\Review\Message\Review\ReviewOpened;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\CodeReview\Activity\CodeReviewActivityProvider;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CodeReviewActivityProvider::class)]
class CodeReviewActivityProviderTest extends AbstractTestCase
{
    private CodeReviewRepository&MockObject $reviewRepository;
    private UserRepository&MockObject       $userRepository;
    private CodeReviewActivityProvider      $activityProvider;

    public function setUp(): void
    {
        parent::setUp();
        $this->reviewRepository = $this->createMock(CodeReviewRepository::class);
        $this->userRepository   = $this->createMock(UserRepository::class);
        $this->activityProvider = new CodeReviewActivityProvider($this->reviewRepository, $this->userRepository);
    }

    public function testFromEvent(): void
    {
        $event  = new ReviewOpened(123, 789);
        $review = new CodeReview();
        $review->setId(123);
        $user = new User();
        $user->setId(789);

        $this->reviewRepository->expects($this->once())->method('find')->with(123)->willReturn($review);
        $this->userRepository->expects($this->once())->method('find')->with(789)->willReturn($user);

        $activity = $this->activityProvider->fromEvent($event);
        static::assertNotNull($activity);
        static::assertSame($review, $activity->getReview());
        static::assertSame($user, $activity->getUser());
        static::assertSame(ReviewOpened::NAME, $activity->getEventName());
        static::assertSame(['reviewId' => 123, 'userId' => 789], $activity->getData());
        static::assertGreaterThan(0, $activity->getCreateTimestamp());
    }

    public function testFromEventAbsentReview(): void
    {
        $event = new ReviewOpened(123, 789);

        $this->reviewRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->userRepository->expects(self::never())->method('find');

        static::assertNull($this->activityProvider->fromEvent($event));
    }

    public function testFromEventWithoutUser(): void
    {
        $event  = new ReviewCreated(123, 789);
        $review = new CodeReview();
        $review->setId(123);

        $this->reviewRepository->expects($this->once())->method('find')->with(123)->willReturn($review);
        $this->userRepository->expects(self::never())->method('find');

        $this->activityProvider->fromEvent($event);
    }
}

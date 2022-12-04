<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\CodeReview;

use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Message\Review\ReviewCreated;
use DR\GitCommitNotification\Message\Review\ReviewOpened;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Repository\User\UserRepository;
use DR\GitCommitNotification\Service\CodeReview\CodeReviewActivityProvider;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\CodeReview\CodeReviewActivityProvider
 * @covers ::__construct
 */
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

    /**
     * @covers ::fromEvent
     */
    public function testFromEvent(): void
    {
        $event  = new ReviewOpened(123, 789);
        $review = new CodeReview();
        $review->setId(123);
        $user = new User();
        $user->setId(789);

        $this->reviewRepository->expects(self::once())->method('find')->with(123)->willReturn($review);
        $this->userRepository->expects(self::once())->method('find')->with(789)->willReturn($user);

        $activity = $this->activityProvider->fromEvent($event);
        static::assertNotNull($activity);
        static::assertSame($review, $activity->getReview());
        static::assertSame($user, $activity->getUser());
        static::assertSame(ReviewOpened::NAME, $activity->getEventName());
        static::assertSame(['reviewId' => 123, 'userId' => 789], $activity->getData());
        static::assertNotNull($activity->getCreateTimestamp());
    }

    /**
     * @covers ::fromEvent
     */
    public function testFromEventAbsentReview(): void
    {
        $event = new ReviewOpened(123, 789);

        $this->reviewRepository->expects(self::once())->method('find')->with(123)->willReturn(null);
        $this->userRepository->expects(self::never())->method('find');

        static::assertNull($this->activityProvider->fromEvent($event));
    }

    /**
     * @covers ::fromEvent
     */
    public function testFromEventWithoutUser(): void
    {
        $event  = new ReviewCreated(123, 789);
        $review = new CodeReview();
        $review->setId(123);

        $this->reviewRepository->expects(self::once())->method('find')->with(123)->willReturn($review);
        $this->userRepository->expects(self::never())->method('find');

        $this->activityProvider->fromEvent($event);
    }
}

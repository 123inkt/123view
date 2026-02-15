<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Review\Entity\User\User;
use DR\Review\Message\CodeReviewAwareInterface;
use DR\Review\Message\Review\ReviewCreated;
use DR\Review\MessageHandler\ReviewActivityMessageHandler;
use DR\Review\Repository\Review\CodeReviewActivityRepository;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\CodeReview\Activity\CodeReviewActivityProvider;
use DR\Review\Service\CodeReview\Activity\CodeReviewActivityPublisher;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(ReviewActivityMessageHandler::class)]
class ReviewActivityMessageHandlerTest extends AbstractTestCase
{
    private CodeReviewActivityProvider&MockObject   $activityProvider;
    private CodeReviewActivityRepository&MockObject $activityRepository;
    private CodeReviewActivityPublisher&MockObject  $activityPublisher;
    private CodeReviewRepository&MockObject         $reviewRepository;
    private UserRepository&MockObject               $userRepository;
    private ReviewActivityMessageHandler            $messageHandler;

    public function setUp(): void
    {
        parent::setUp();
        $this->activityProvider   = $this->createMock(CodeReviewActivityProvider::class);
        $this->activityRepository = $this->createMock(CodeReviewActivityRepository::class);
        $this->activityPublisher  = $this->createMock(CodeReviewActivityPublisher::class);
        $this->reviewRepository   = $this->createMock(CodeReviewRepository::class);
        $this->userRepository     = $this->createMock(UserRepository::class);
        $this->messageHandler     = new ReviewActivityMessageHandler(
            $this->activityProvider,
            $this->activityRepository,
            $this->reviewRepository,
            $this->userRepository,
            $this->activityPublisher
        );
    }

    /**
     * @throws Throwable
     */
    public function testInvokeWithUnsupportedEvent(): void
    {
        $event = static::createStub(CodeReviewAwareInterface::class);

        $this->activityRepository->expects($this->never())->method('save');
        $this->activityProvider->expects($this->never())->method('fromEvent');
        $this->activityPublisher->expects($this->never())->method('publish');
        $this->reviewRepository->expects($this->never())->method('save');
        $this->userRepository->expects($this->never())->method('getActors');
        ($this->messageHandler)($event);
    }

    /**
     * @throws Throwable
     */
    public function testInvoke(): void
    {
        $user = new User();
        $user->setId(135);
        $event = new ReviewCreated(123, 456);

        $review = new CodeReview();
        $review->setId(948);
        $activity = new CodeReviewActivity();
        $activity->setReview($review);

        // setup mocks
        $this->activityProvider->expects($this->once())->method('fromEvent')->with($event)->willReturn($activity);
        $this->activityRepository->expects($this->once())->method('save')->with($activity, true);
        $this->userRepository->expects($this->once())->method('getActors')->with(948)->willReturn([$user]);
        $this->reviewRepository->expects($this->once())->method('save')->with($review, true);
        $this->activityPublisher->expects($this->once())->method('publish')->with($activity);

        // execute test
        ($this->messageHandler)($event);

        static::assertSame([135], $review->getActors());
        static::assertGreaterThan(0, $review->getUpdateTimestamp());
    }
}

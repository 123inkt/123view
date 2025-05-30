<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\ChangeReviewStateController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Request\Review\ChangeReviewStateRequest;
use DR\Review\Service\Webhook\ReviewEventService;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @extends AbstractControllerTestCase<ChangeReviewStateController>
 */
#[CoversClass(ChangeReviewStateController::class)]
class ChangeReviewStateControllerTest extends AbstractControllerTestCase
{
    private CodeReviewRepository&MockObject $reviewRepository;
    private ReviewEventService&MockObject   $eventService;

    public function setUp(): void
    {
        $this->reviewRepository = $this->createMock(CodeReviewRepository::class);
        $this->eventService     = $this->createMock(ReviewEventService::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $request = $this->createMock(ChangeReviewStateRequest::class);
        $request->expects($this->once())->method('getState')->willReturn(CodeReviewStateType::OPEN);

        $review = new CodeReview();
        $review->setId(123);
        $review->setState(CodeReviewStateType::CLOSED);

        $user = new User();
        $user->setId(456);

        $this->expectGetUser($user);
        $this->reviewRepository->expects($this->once())->method('save')->with($review, true);
        $this->eventService->expects($this->once())->method('reviewStateChanged')->with($review, CodeReviewStateType::CLOSED, 456);

        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);

        ($this->controller)($request, $review);

        static::assertSame(CodeReviewStateType::OPEN, $review->getState());
    }

    public function getController(): AbstractController
    {
        return new ChangeReviewStateController($this->reviewRepository, $this->eventService);
    }
}

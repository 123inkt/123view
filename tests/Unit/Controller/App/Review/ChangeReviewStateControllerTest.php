<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\App\Review;

use DR\GitCommitNotification\Controller\App\Review\ChangeReviewStateController;
use DR\GitCommitNotification\Controller\App\Review\ReviewController;
use DR\GitCommitNotification\Doctrine\Type\CodeReviewStateType;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Request\Review\ChangeReviewStateRequest;
use DR\GitCommitNotification\Service\Webhook\ReviewEventService;
use DR\GitCommitNotification\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\App\Review\ChangeReviewStateController
 * @covers ::__construct
 */
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

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $request = $this->createMock(ChangeReviewStateRequest::class);
        $request->expects(self::once())->method('getState')->willReturn(CodeReviewStateType::OPEN);

        $review = new CodeReview();
        $review->setId(123);
        $review->setState(CodeReviewStateType::CLOSED);

        $this->reviewRepository->expects(self::once())->method('save')->with($review, true);
        $this->eventService->expects(self::once())->method('reviewStateChanged')->with($review, CodeReviewStateType::CLOSED);

        $this->expectRefererRedirect(ReviewController::class, ['id' => 123]);

        ($this->controller)($request, $review);

        static::assertSame(CodeReviewStateType::OPEN, $review->getState());
    }

    public function getController(): AbstractController
    {
        return new ChangeReviewStateController($this->reviewRepository, $this->eventService);
    }
}

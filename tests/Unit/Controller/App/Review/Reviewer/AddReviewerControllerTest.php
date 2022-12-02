<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\App\Review\Reviewer;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Controller\App\Review\ReviewController;
use DR\GitCommitNotification\Controller\App\Review\Reviewer\AddReviewerController;
use DR\GitCommitNotification\Doctrine\Type\CodeReviewerStateType;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\CodeReviewer;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Form\Review\AddReviewerFormType;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Service\Git\Review\CodeReviewerService;
use DR\GitCommitNotification\Service\Webhook\ReviewEventService;
use DR\GitCommitNotification\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\App\Review\Reviewer\AddReviewerController
 * @covers ::__construct
 */
class AddReviewerControllerTest extends AbstractControllerTestCase
{
    private CodeReviewRepository&MockObject $reviewRepository;
    private CodeReviewerService&MockObject  $reviewerService;
    private ReviewEventService&MockObject   $eventService;

    public function setUp(): void
    {
        $this->reviewRepository = $this->createMock(CodeReviewRepository::class);
        $this->reviewerService  = $this->createMock(CodeReviewerService::class);
        $this->eventService     = $this->createMock(ReviewEventService::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeNotSubmitted(): void
    {
        $request = new Request();
        $review  = new CodeReview();
        $review->setId(123);

        $this->expectCreateForm(AddReviewerFormType::class, null, ['review' => $review])
            ->handleRequest($request)
            ->isValidWillReturn(false);
        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);

        ($this->controller)($request, $review);
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeSubmittedWithoutUser(): void
    {
        $request = new Request();
        $review  = new CodeReview();
        $review->setId(123);

        $this->expectCreateForm(AddReviewerFormType::class, null, ['review' => $review])
            ->handleRequest($request)
            ->isValidWillReturn(true)
            ->getDataWillReturn(['user' => null]);
        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);

        ($this->controller)($request, $review);
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeSubmitted(): void
    {
        $request = new Request();
        $review  = new CodeReview();
        $review->setId(123);
        $reviewer = new CodeReviewer();
        $user     = new User();
        $user->setId(456);

        $this->expectCreateForm(AddReviewerFormType::class, null, ['review' => $review])
            ->handleRequest($request)
            ->isValidWillReturn(true)
            ->getDataWillReturn(['user' => $user]);

        $this->expectGetUser($user);
        $this->reviewerService->expects(self::once())->method('addReviewer')->with($review, $user)->willReturn($reviewer);
        $this->reviewRepository->expects(self::once())->method('save')->with($review, true);

        $this->eventService->expects(self::once())->method('reviewerAdded')->with($review, $reviewer, 456, true);
        $this->eventService->expects(self::once())->method('reviewerStateChanged')->with($review, CodeReviewerStateType::OPEN, 456);

        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);

        ($this->controller)($request, $review);
    }

    public function getController(): AbstractController
    {
        return new AddReviewerController($this->reviewRepository, $this->reviewerService, $this->eventService);
    }
}

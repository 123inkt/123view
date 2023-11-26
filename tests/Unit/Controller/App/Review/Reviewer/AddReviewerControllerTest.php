<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Reviewer;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Controller\App\Review\Reviewer\AddReviewerController;
use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\User\User;
use DR\Review\Form\Review\AddReviewerFormType;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Git\Review\CodeReviewerService;
use DR\Review\Service\Webhook\ReviewEventService;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(AddReviewerController::class)]
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
        $this->eventService->expects(self::once())->method('reviewReviewerStateChanged')->with($review, CodeReviewerStateType::OPEN, 456);

        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);

        ($this->controller)($request, $review);
    }

    public function getController(): AbstractController
    {
        return new AddReviewerController($this->reviewRepository, $this->reviewerService, $this->eventService);
    }
}

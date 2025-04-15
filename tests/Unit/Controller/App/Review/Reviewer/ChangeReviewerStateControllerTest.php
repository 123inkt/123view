<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Reviewer;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Controller\App\Review\Reviewer\ChangeReviewerStateController;
use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\User\User;
use DR\Review\Request\Review\ChangeReviewerStateRequest;
use DR\Review\Service\CodeReview\ChangeReviewerStateService;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @extends AbstractControllerTestCase<ChangeReviewerStateController>
 */
#[CoversClass(ChangeReviewerStateController::class)]
class ChangeReviewerStateControllerTest extends AbstractControllerTestCase
{
    private ChangeReviewerStateService&MockObject $changeReviewerStateService;

    public function setUp(): void
    {
        $this->changeReviewerStateService = $this->createMock(ChangeReviewerStateService::class);
        parent::setUp();
    }

    public function testInvokeExistingReviewerChangesState(): void
    {
        $request = $this->createMock(ChangeReviewerStateRequest::class);
        $request->expects(self::once())->method('getState')->willReturn(CodeReviewerStateType::ACCEPTED);

        $user   = (new User())->setId(789);
        $review = (new CodeReview())->setId(123);

        $this->expectGetUser($user);
        $this->changeReviewerStateService->expects(self::once())->method('changeState')->with($review, $user, CodeReviewerStateType::ACCEPTED);
        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);

        ($this->controller)($request, $review);
    }

    public function getController(): AbstractController
    {
        return new ChangeReviewerStateController($this->changeReviewerStateService);
    }
}

<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\App\Review\Reviewer;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Controller\App\Review\ReviewController;
use DR\GitCommitNotification\Controller\App\Review\Reviewer\ChangeReviewerStateController;
use DR\GitCommitNotification\Doctrine\Type\CodeReviewerStateType;
use DR\GitCommitNotification\Doctrine\Type\CodeReviewStateType;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\CodeReviewer;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Request\Review\ChangeReviewerStateRequest;
use DR\GitCommitNotification\Service\Git\Review\CodeReviewerService;
use DR\GitCommitNotification\Service\Webhook\ReviewEventService;
use DR\GitCommitNotification\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\App\Review\Reviewer\ChangeReviewerStateController
 * @covers ::__construct
 */
class ChangeReviewerStateControllerTest extends AbstractControllerTestCase
{
    private ManagerRegistry&MockObject     $registry;
    private ReviewEventService&MockObject  $eventService;
    private CodeReviewerService&MockObject $reviewerService;
    private ObjectManager&MockObject       $objectManager;

    public function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->registry      = $this->createMock(ManagerRegistry::class);
        $this->registry->method('getManager')->willReturn($this->objectManager);
        $this->eventService    = $this->createMock(ReviewEventService::class);
        $this->reviewerService = $this->createMock(CodeReviewerService::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeExistingReviewerChangesState(): void
    {
        $request = $this->createMock(ChangeReviewerStateRequest::class);
        $request->expects(self::once())->method('getState')->willReturn(CodeReviewerStateType::ACCEPTED);

        $user     = new User();
        $reviewer = new CodeReviewer();
        $reviewer->setUser($user);
        $review = new CodeReview();
        $review->setId(123);
        $review->getReviewers()->add($reviewer);

        $this->expectGetUser($user);
        $this->reviewerService->expects(self::once())->method('setReviewerState')->with($review, $reviewer, CodeReviewerStateType::ACCEPTED);

        $this->objectManager->expects(self::exactly(2))->method('persist')->withConsecutive([$review], [$reviewer]);
        $this->objectManager->expects(self::once())->method('flush');

        $this->eventService->expects(self::once())->method('reviewerAdded')->with($review, $reviewer, false);
        $this->eventService->expects(self::once())->method('reviewReviewerStateChanged')->with($review, CodeReviewerStateType::OPEN);
        $this->eventService->expects(self::once())->method('reviewStateChanged')->with($review, CodeReviewStateType::OPEN);

        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);

        ($this->controller)($request, $review);
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeNewReviewerChangesState(): void
    {
        $request = $this->createMock(ChangeReviewerStateRequest::class);
        $request->expects(self::once())->method('getState')->willReturn(CodeReviewerStateType::ACCEPTED);

        $user = new User();
        $user->setId(456);
        $reviewer = new CodeReviewer();
        $reviewer->setUser($user);
        $review = new CodeReview();
        $review->setId(123);

        $this->expectGetUser($user);
        $this->reviewerService->expects(self::once())->method('addReviewer')->with($review, $user)->willReturn($reviewer);
        $this->reviewerService->expects(self::once())->method('setReviewerState')->with($review, $reviewer, CodeReviewerStateType::ACCEPTED);

        $this->objectManager->expects(self::exactly(2))->method('persist')->withConsecutive([$review], [$reviewer]);
        $this->objectManager->expects(self::once())->method('flush');

        $this->eventService->expects(self::once())->method('reviewerAdded')->with($review, $reviewer, 456, true);
        $this->eventService->expects(self::once())->method('reviewReviewerStateChanged')->with($review, CodeReviewerStateType::OPEN, 456);
        $this->eventService->expects(self::once())->method('reviewStateChanged')->with($review, CodeReviewStateType::OPEN, 456);

        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);

        ($this->controller)($request, $review);
    }

    public function getController(): AbstractController
    {
        return new ChangeReviewerStateController($this->registry, $this->eventService, $this->reviewerService);
    }
}

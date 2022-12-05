<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Reviewer;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Controller\App\Review\Reviewer\RemoveReviewerController;
use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\User\User;
use DR\Review\Service\Webhook\ReviewEventService;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Controller\App\Review\Reviewer\RemoveReviewerController
 * @covers ::__construct
 */
class RemoveReviewerControllerTest extends AbstractControllerTestCase
{
    private ManagerRegistry&MockObject    $registry;
    private ReviewEventService&MockObject $eventService;
    private ObjectManager&MockObject      $objectManager;

    public function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->registry      = $this->createMock(ManagerRegistry::class);
        $this->registry->method('getManager')->willReturn($this->objectManager);
        $this->eventService = $this->createMock(ReviewEventService::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $reviewerA = new CodeReviewer();
        $reviewerA->setState(CodeReviewerStateType::ACCEPTED);
        $reviewerB = new CodeReviewer();
        $reviewerB->setState(CodeReviewerStateType::ACCEPTED);
        $comment = new Comment();
        $review  = new CodeReview();
        $review->setId(123);
        $review->setState(CodeReviewStateType::CLOSED);
        $review->getComments()->add($comment);
        $review->getReviewers()->add($reviewerA);
        $review->getReviewers()->add($reviewerB);

        $user = new User();
        $user->setId(456);

        $this->expectGetUser($user);
        $this->objectManager->expects(self::once())->method('remove')->with($reviewerB);
        $this->objectManager->expects(self::once())->method('persist')->with($review);
        $this->objectManager->expects(self::once())->method('flush');

        $this->eventService->expects(self::once())->method('reviewerRemoved')->with($review, $reviewerB, 456);
        $this->eventService->expects(self::once())->method('reviewReviewerStateChanged')->with($review, CodeReviewerStateType::ACCEPTED, 456);
        $this->eventService->expects(self::once())->method('reviewStateChanged')->with($review, CodeReviewStateType::CLOSED, 456);

        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);

        ($this->controller)($review, $reviewerB);

        static::assertSame(CommentStateType::RESOLVED, $comment->getState());
        static::assertSame(CodeReviewStateType::CLOSED, $review->getState());
    }

    public function getController(): AbstractController
    {
        return new RemoveReviewerController($this->registry, $this->eventService);
    }
}

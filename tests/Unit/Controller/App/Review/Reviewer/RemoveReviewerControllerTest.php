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
use DR\Review\Service\CodeReview\CodeReviewerStateResolver;
use DR\Review\Service\Webhook\ReviewEventService;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;

/**
 * @extends AbstractControllerTestCase<RemoveReviewerController>
 */
#[CoversClass(RemoveReviewerController::class)]
class RemoveReviewerControllerTest extends AbstractControllerTestCase
{
    private ManagerRegistry&Stub           $registry;
    private ReviewEventService&MockObject        $eventService;
    private CodeReviewerStateResolver&MockObject $reviewerStateResolver;
    private ObjectManager&MockObject             $objectManager;

    public function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->registry      = static::createStub(ManagerRegistry::class);
        $this->registry->method('getManager')->willReturn($this->objectManager);
        $this->reviewerStateResolver = $this->createMock(CodeReviewerStateResolver::class);
        $this->eventService          = $this->createMock(ReviewEventService::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $reviewerA = new CodeReviewer();
        $reviewerB = new CodeReviewer();
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
        $this->objectManager->expects($this->once())->method('remove')->with($reviewerB);
        $this->objectManager->expects($this->once())->method('persist')->with($review);
        $this->objectManager->expects($this->once())->method('flush');

        $this->reviewerStateResolver->expects($this->once())->method('getReviewersState')->with($review)->willReturn(CodeReviewerStateType::ACCEPTED);

        $this->eventService->expects($this->once())->method('reviewerRemoved')->with($review, $reviewerB, 456);
        $this->eventService->expects($this->once())->method('reviewReviewerStateChanged')->with($review, CodeReviewerStateType::ACCEPTED, 456);
        $this->eventService->expects($this->once())->method('reviewStateChanged')->with($review, CodeReviewStateType::CLOSED, 456);

        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);

        ($this->controller)($review, $reviewerB);

        static::assertSame(CommentStateType::RESOLVED, $comment->getState());
        static::assertSame(CodeReviewStateType::CLOSED, $review->getState());
    }

    public function getController(): AbstractController
    {
        return new RemoveReviewerController($this->registry, $this->reviewerStateResolver, $this->eventService);
    }
}

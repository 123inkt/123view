<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\User\User;
use DR\Review\Service\CodeReview\ChangeReviewerStateService;
use DR\Review\Service\CodeReview\CodeReviewerStateResolver;
use DR\Review\Service\Git\Review\CodeReviewerService;
use DR\Review\Service\Webhook\ReviewEventService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(ChangeReviewerStateService::class)]
class ChangeReviewerStateServiceTest extends AbstractTestCase
{
    private ReviewEventService&MockObject        $eventService;
    private CodeReviewerService&MockObject       $reviewerService;
    private CodeReviewerStateResolver&MockObject $reviewerStateResolver;
    private ObjectManager&MockObject             $objectManager;
    private ChangeReviewerStateService           $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->objectManager = $this->createMock(ObjectManager::class);
        $registry            = static::createStub(ManagerRegistry::class);
        $registry->method('getManager')->willReturn($this->objectManager);
        $this->eventService          = $this->createMock(ReviewEventService::class);
        $this->reviewerService       = $this->createMock(CodeReviewerService::class);
        $this->reviewerStateResolver = $this->createMock(CodeReviewerStateResolver::class);
        $this->service               = new ChangeReviewerStateService(
            $registry,
            $this->eventService,
            $this->reviewerService,
            $this->reviewerStateResolver
        );
    }

    public function testChangeStateExistingReviewerChangesState(): void
    {
        $user     = (new User())->setId(789);
        $reviewer = new CodeReviewer();
        $reviewer->setUser($user);
        $review = new CodeReview();
        $review->setId(123);
        $review->getReviewers()->add($reviewer);

        $this->reviewerService->expects($this->once())->method('setReviewerState')->with($review, $reviewer, CodeReviewerStateType::ACCEPTED);

        $this->objectManager->expects($this->exactly(2))->method('persist')->with(...consecutive([$review], [$reviewer]));
        $this->objectManager->expects($this->once())->method('flush');

        $this->reviewerStateResolver->expects($this->once())->method('getReviewersState')->with($review)->willReturn(CodeReviewerStateType::OPEN);

        $this->eventService->expects($this->once())->method('reviewerAdded')->with($review, $reviewer, 789, false);
        $this->eventService->expects($this->once())->method('reviewReviewerStateChanged')->with($review, CodeReviewerStateType::OPEN);
        $this->eventService->expects($this->once())->method('reviewStateChanged')->with($review, CodeReviewStateType::OPEN);

        $this->service->changeState($review, $user, CodeReviewerStateType::ACCEPTED);
    }

    public function testChangeStateNewReviewerChangesState(): void
    {
        $user = new User();
        $user->setId(456);
        $reviewer = new CodeReviewer();
        $reviewer->setUser($user);
        $review = new CodeReview();
        $review->setId(123);

        $this->reviewerService->expects($this->once())->method('addReviewer')->with($review, $user)->willReturn($reviewer);
        $this->reviewerService->expects($this->once())->method('setReviewerState')->with($review, $reviewer, CodeReviewerStateType::ACCEPTED);

        $this->objectManager->expects($this->exactly(2))->method('persist')->with(...consecutive([$review], [$reviewer]));
        $this->objectManager->expects($this->once())->method('flush');

        $this->reviewerStateResolver->expects($this->once())->method('getReviewersState')->with($review)->willReturn(CodeReviewerStateType::OPEN);

        $this->eventService->expects($this->once())->method('reviewerAdded')->with($review, $reviewer, 456, true);
        $this->eventService->expects($this->once())->method('reviewReviewerStateChanged')->with($review, CodeReviewerStateType::OPEN, 456);
        $this->eventService->expects($this->once())->method('reviewStateChanged')->with($review, CodeReviewStateType::OPEN, 456);

        $this->service->changeState($review, $user, CodeReviewerStateType::ACCEPTED);
    }
}

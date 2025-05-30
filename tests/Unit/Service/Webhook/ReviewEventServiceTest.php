<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Webhook;

use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Message\Review\ReviewAccepted;
use DR\Review\Message\Review\ReviewClosed;
use DR\Review\Message\Review\ReviewOpened;
use DR\Review\Message\Review\ReviewRejected;
use DR\Review\Message\Review\ReviewResumed;
use DR\Review\Message\Reviewer\ReviewerAdded;
use DR\Review\Message\Reviewer\ReviewerRemoved;
use DR\Review\Message\Reviewer\ReviewerStateChanged;
use DR\Review\Message\Revision\ReviewRevisionAdded;
use DR\Review\Message\Revision\ReviewRevisionRemoved;
use DR\Review\Service\CodeReview\CodeReviewerStateResolver;
use DR\Review\Service\Webhook\ReviewEventService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Messenger\MessageBusInterface;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(ReviewEventService::class)]
class ReviewEventServiceTest extends AbstractTestCase
{
    private CodeReviewerStateResolver&MockObject $reviewerStateResolver;
    private MessageBusInterface&MockObject       $bus;
    private ReviewEventService                   $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->reviewerStateResolver = $this->createMock(CodeReviewerStateResolver::class);
        $this->bus                   = $this->createMock(MessageBusInterface::class);
        $this->service               = new ReviewEventService($this->reviewerStateResolver, $this->bus);
    }

    public function testReviewerAdded(): void
    {
        $user = new User();
        $user->setId(456);

        $reviewer = new CodeReviewer();
        $reviewer->setUser($user);

        $review = new CodeReview();
        $review->setId(123);
        $review->getReviewers()->add($reviewer);

        $this->bus->expects($this->once())->method('dispatch')->with(new ReviewerAdded(123, 456, 5))->willReturn($this->envelope);

        $this->service->reviewerAdded($review, $reviewer, 5, false);
        $this->service->reviewerAdded($review, $reviewer, 5, true);
    }

    public function testReviewerRemoved(): void
    {
        $user = new User();
        $user->setId(456);

        $reviewer = new CodeReviewer();
        $reviewer->setUser($user);

        $review = new CodeReview();
        $review->setId(123);
        $review->getReviewers()->add($reviewer);

        $this->bus->expects($this->once())->method('dispatch')->with(new ReviewerRemoved(123, 456, 5))->willReturn($this->envelope);

        $this->service->reviewerRemoved($review, $reviewer, 5);
    }

    public function testReviewReviewerStateChanged(): void
    {
        $user = new User();
        $user->setId(456);

        $reviewer = new CodeReviewer();
        $reviewer->setUser($user);

        $review = new CodeReview();
        $review->setId(123);
        $review->getReviewers()->add($reviewer);

        $this->reviewerStateResolver->expects($this->exactly(4))
            ->method('getReviewersState')
            ->with($review)
            ->willReturn(
                CodeReviewerStateType::REJECTED,
                CodeReviewerStateType::REJECTED,
                CodeReviewerStateType::ACCEPTED,
                CodeReviewerStateType::OPEN
            );

        $this->bus->expects($this->exactly(3))
            ->method('dispatch')
            ->with(
                ...consecutive(
                    [new ReviewRejected(123, 5)],
                    [new ReviewAccepted(123, 5)],
                    [new ReviewResumed(123, 5)],
                )
            )
            ->willReturn($this->envelope);

        $this->service->reviewReviewerStateChanged($review, CodeReviewerStateType::REJECTED, 5);
        $this->service->reviewReviewerStateChanged($review, CodeReviewerStateType::OPEN, 5);
        $this->service->reviewReviewerStateChanged($review, CodeReviewerStateType::OPEN, 5);
        $this->service->reviewReviewerStateChanged($review, CodeReviewerStateType::REJECTED, 5);
    }

    public function testReviewerStateChanged(): void
    {
        $user = new User();
        $user->setId(789);
        $review = new CodeReview();
        $review->setId(123);
        $reviewer = new CodeReviewer();
        $reviewer->setId(456);
        $reviewer->setState(CodeReviewerStateType::ACCEPTED);
        $reviewer->setUser($user);

        $this->bus->expects($this->once())
            ->method('dispatch')
            ->with(new ReviewerStateChanged(123, 456, 789, CodeReviewerStateType::REJECTED, CodeReviewerStateType::ACCEPTED))
            ->willReturn($this->envelope);

        // first test without state change
        $this->service->reviewerStateChanged($review, $reviewer, CodeReviewerStateType::ACCEPTED);

        // test with state change
        $this->service->reviewerStateChanged($review, $reviewer, CodeReviewerStateType::REJECTED);
    }

    public function testReviewStateChanged(): void
    {
        $review = new CodeReview();
        $review->setId(123);

        $this->bus->expects($this->exactly(2))
            ->method('dispatch')
            ->with(
                ...consecutive(
                    [new ReviewOpened(123, 5)],
                    [new ReviewClosed(123, 5)],
                )
            )
            ->willReturn($this->envelope);

        $review->setState(CodeReviewStateType::OPEN);
        $this->service->reviewStateChanged($review, CodeReviewStateType::OPEN, 5);

        $review->setState(CodeReviewStateType::OPEN);
        $this->service->reviewStateChanged($review, CodeReviewStateType::CLOSED, 5);

        $review->setState(CodeReviewStateType::CLOSED);
        $this->service->reviewStateChanged($review, CodeReviewStateType::OPEN, 5);
    }

    public function testRevisionsAdded(): void
    {
        $revisionA = new Revision();
        $revisionA->setId(456);
        $revisionA->setTitle('title');
        $revisionB = new Revision();
        $revisionB->setId(789);
        $revisionB->setTitle('title');

        $review = new CodeReview();
        $review->setId(123);

        $this->bus->expects($this->exactly(2))
            ->method('dispatch')
            ->with(
                ...consecutive(
                    [new ReviewRevisionAdded(123, 456, 5, 'title')],
                    [new ReviewRevisionAdded(123, 789, 5, 'title')],
                )
            )
            ->willReturn($this->envelope);

        $this->service->revisionsAdded($review, [$revisionA, $revisionB], 5);
    }

    public function testDetachRevisions(): void
    {
        $revisionA = new Revision();
        $revisionA->setId(456);
        $revisionA->setTitle('title');
        $revisionB = new Revision();
        $revisionB->setId(789);
        $revisionB->setTitle('title');

        $review = new CodeReview();
        $review->setId(123);

        $this->bus->expects($this->exactly(2))
            ->method('dispatch')
            ->with(
                ...consecutive(
                    [new ReviewRevisionRemoved(123, 456, 5, 'title')],
                    [new ReviewRevisionRemoved(123, 789, 5, 'title')],
                )
            )
            ->willReturn($this->envelope);

        $this->service->revisionsDetached($review, [$revisionA, $revisionB], 5);
    }
}

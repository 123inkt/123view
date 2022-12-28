<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Webhook;

use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\Review\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Message\Review\ReviewAccepted;
use DR\Review\Message\Review\ReviewClosed;
use DR\Review\Message\Review\ReviewCreated;
use DR\Review\Message\Review\ReviewOpened;
use DR\Review\Message\Review\ReviewRejected;
use DR\Review\Message\Review\ReviewResumed;
use DR\Review\Message\Reviewer\ReviewerAdded;
use DR\Review\Message\Reviewer\ReviewerRemoved;
use DR\Review\Message\Reviewer\ReviewerStateChanged;
use DR\Review\Message\Revision\ReviewRevisionAdded;
use DR\Review\Message\Revision\ReviewRevisionRemoved;
use DR\Review\Service\Webhook\ReviewEventService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \DR\Review\Service\Webhook\ReviewEventService
 * @covers ::__construct
 */
class ReviewEventServiceTest extends AbstractTestCase
{
    private MessageBusInterface&MockObject $bus;
    private ReviewEventService             $service;
    private Envelope                       $envelope;

    public function setUp(): void
    {
        parent::setUp();
        $this->envelope = new Envelope(new stdClass(), []);
        $this->bus      = $this->createMock(MessageBusInterface::class);
        $this->service  = new ReviewEventService($this->bus);
    }

    /**
     * @covers ::reviewerAdded
     */
    public function testReviewerAdded(): void
    {
        $user = new User();
        $user->setId(456);

        $reviewer = new CodeReviewer();
        $reviewer->setUser($user);

        $review = new CodeReview();
        $review->setId(123);
        $review->getReviewers()->add($reviewer);

        $this->bus->expects(self::once())->method('dispatch')->with(new ReviewerAdded(123, 456, 5))->willReturn($this->envelope);

        $this->service->reviewerAdded($review, $reviewer, 5, false);
        $this->service->reviewerAdded($review, $reviewer, 5, true);
    }

    /**
     * @covers ::reviewerRemoved
     */
    public function testReviewerRemoved(): void
    {
        $user = new User();
        $user->setId(456);

        $reviewer = new CodeReviewer();
        $reviewer->setUser($user);

        $review = new CodeReview();
        $review->setId(123);
        $review->getReviewers()->add($reviewer);

        $this->bus->expects(self::once())->method('dispatch')->with(new ReviewerRemoved(123, 456, 5))->willReturn($this->envelope);

        $this->service->reviewerRemoved($review, $reviewer, 5);
    }

    /**
     * @covers ::reviewReviewerStateChanged
     */
    public function testReviewReviewerStateChanged(): void
    {
        $user = new User();
        $user->setId(456);

        $reviewer = new CodeReviewer();
        $reviewer->setUser($user);

        $review = new CodeReview();
        $review->setId(123);
        $review->getReviewers()->add($reviewer);

        $this->bus->expects(self::exactly(3))
            ->method('dispatch')
            ->withConsecutive(
                [new ReviewRejected(123, 5)],
                [new ReviewAccepted(123, 5)],
                [new ReviewResumed(123, 5)],
            )
            ->willReturn($this->envelope);

        $reviewer->setState(CodeReviewerStateType::REJECTED);
        $this->service->reviewReviewerStateChanged($review, CodeReviewerStateType::REJECTED, 5);

        $reviewer->setState(CodeReviewerStateType::REJECTED);
        $this->service->reviewReviewerStateChanged($review, CodeReviewerStateType::OPEN, 5);

        $reviewer->setState(CodeReviewerStateType::ACCEPTED);
        $this->service->reviewReviewerStateChanged($review, CodeReviewerStateType::OPEN, 5);

        $reviewer->setState(CodeReviewerStateType::OPEN);
        $this->service->reviewReviewerStateChanged($review, CodeReviewerStateType::REJECTED, 5);
    }

    /**
     * @covers ::reviewerStateChanged
     */
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

        $this->bus->expects(self::once())
            ->method('dispatch')
            ->with(new ReviewerStateChanged(123, 456, 789, CodeReviewerStateType::REJECTED, CodeReviewerStateType::ACCEPTED))
            ->willReturn($this->envelope);

        // first test without state change
        $this->service->reviewerStateChanged($review, $reviewer, CodeReviewerStateType::ACCEPTED);

        // test with state change
        $this->service->reviewerStateChanged($review, $reviewer, CodeReviewerStateType::REJECTED);
    }

    /**
     * @covers ::reviewStateChanged
     */
    public function testReviewStateChanged(): void
    {
        $review = new CodeReview();
        $review->setId(123);

        $this->bus->expects(self::exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [new ReviewOpened(123, 5)],
                [new ReviewClosed(123, 5)],
            )
            ->willReturn($this->envelope);

        $review->setState(CodeReviewStateType::OPEN);
        $this->service->reviewStateChanged($review, CodeReviewStateType::OPEN, 5);

        $review->setState(CodeReviewStateType::OPEN);
        $this->service->reviewStateChanged($review, CodeReviewStateType::CLOSED, 5);

        $review->setState(CodeReviewStateType::CLOSED);
        $this->service->reviewStateChanged($review, CodeReviewStateType::OPEN, 5);
    }

    /**
     * @covers ::revisionsAdded
     */
    public function testRevisionsAdded(): void
    {
        $revisionA = new Revision();
        $revisionA->setId(456);
        $revisionB = new Revision();
        $revisionB->setId(789);

        $review = new CodeReview();
        $review->setId(123);

        $this->bus->expects(self::exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [new ReviewRevisionAdded(123, 456, 5)],
                [new ReviewRevisionAdded(123, 789, 5)],
            )
            ->willReturn($this->envelope);

        $this->service->revisionsAdded($review, [$revisionA, $revisionB], 5);
    }

    /**
     * @covers ::revisionsDetached
     */
    public function testDetachRevisions(): void
    {
        $revisionA = new Revision();
        $revisionA->setId(456);
        $revisionB = new Revision();
        $revisionB->setId(789);

        $review = new CodeReview();
        $review->setId(123);

        $this->bus->expects(self::exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [new ReviewRevisionRemoved(123, 456, 5)],
                [new ReviewRevisionRemoved(123, 789, 5)],
            )
            ->willReturn($this->envelope);

        $this->service->revisionsDetached($review, [$revisionA, $revisionB], 5);
    }

    /**
     * @covers ::revisionAddedToReview
     */
    public function testRevisionAddedToReview(): void
    {
        $revision = new Revision();
        $revision->setId(456);
        $review = new CodeReview();
        $review->setId(123);
        $review->setState(CodeReviewStateType::OPEN);

        $this->bus->expects(self::exactly(4))
            ->method('dispatch')
            ->withConsecutive(
                [new Envelope(new ReviewCreated(123, 456))],
                [new Envelope(new ReviewOpened(123, null))],
                [new Envelope(new ReviewResumed(123, null))],
                [new Envelope(new ReviewRevisionAdded(123, 456, null))],
            )
            ->willReturn($this->envelope);

        $this->service->revisionAddedToReview($review, $revision, true, CodeReviewStateType::CLOSED, CodeReviewerStateType::ACCEPTED);
    }

    /**
     * @covers ::revisionAddedToReview
     */
    public function testRevisionAddedToReviewWithMinimalEvents(): void
    {
        $revision = new Revision();
        $revision->setId(456);
        $review = new CodeReview();
        $review->setId(123);
        $review->setState(CodeReviewStateType::OPEN);

        $this->bus->expects(self::once())
            ->method('dispatch')
            ->with(new Envelope(new ReviewRevisionAdded(123, 456, null)))
            ->willReturn($this->envelope);

        $this->service->revisionAddedToReview($review, $revision, false, CodeReviewStateType::OPEN, CodeReviewerStateType::OPEN);
    }
}

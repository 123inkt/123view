<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Webhook;

use DR\GitCommitNotification\Doctrine\Type\CodeReviewerStateType;
use DR\GitCommitNotification\Doctrine\Type\CodeReviewStateType;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\CodeReviewer;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Message\Review\ReviewAccepted;
use DR\GitCommitNotification\Message\Review\ReviewClosed;
use DR\GitCommitNotification\Message\Review\ReviewOpened;
use DR\GitCommitNotification\Message\Review\ReviewRejected;
use DR\GitCommitNotification\Message\Review\ReviewResumed;
use DR\GitCommitNotification\Message\Reviewer\ReviewerAdded;
use DR\GitCommitNotification\Message\Reviewer\ReviewerRemoved;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionAdded;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionRemoved;
use DR\GitCommitNotification\Service\Webhook\ReviewEventService;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Webhook\ReviewEventService
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

        $this->bus->expects(self::once())->method('dispatch')->with(new ReviewerAdded(123, 456))->willReturn($this->envelope);

        $this->service->reviewerAdded($review, $reviewer, false);
        $this->service->reviewerAdded($review, $reviewer, true);
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

        $this->bus->expects(self::once())->method('dispatch')->with(new ReviewerRemoved(123, 456))->willReturn($this->envelope);

        $this->service->reviewerRemoved($review, $reviewer);
    }

    /**
     * @covers ::reviewerStateChanged
     */
    public function testReviewerStateChanged(): void
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
                [new ReviewRejected(123)],
                [new ReviewAccepted(123)],
                [new ReviewResumed(123)],
            )
            ->willReturn($this->envelope);

        $reviewer->setState(CodeReviewerStateType::REJECTED);
        $this->service->reviewerStateChanged($review, CodeReviewerStateType::REJECTED);

        $reviewer->setState(CodeReviewerStateType::REJECTED);
        $this->service->reviewerStateChanged($review, CodeReviewerStateType::OPEN);

        $reviewer->setState(CodeReviewerStateType::ACCEPTED);
        $this->service->reviewerStateChanged($review, CodeReviewerStateType::OPEN);

        $reviewer->setState(CodeReviewerStateType::OPEN);
        $this->service->reviewerStateChanged($review, CodeReviewerStateType::REJECTED);
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
                [new ReviewOpened(123)],
                [new ReviewClosed(123)],
            )
            ->willReturn($this->envelope);

        $review->setState(CodeReviewStateType::OPEN);
        $this->service->reviewStateChanged($review, CodeReviewStateType::OPEN);

        $review->setState(CodeReviewStateType::OPEN);
        $this->service->reviewStateChanged($review, CodeReviewStateType::CLOSED);

        $review->setState(CodeReviewStateType::CLOSED);
        $this->service->reviewStateChanged($review, CodeReviewStateType::OPEN);
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
                [new ReviewRevisionAdded(123, 456)],
                [new ReviewRevisionAdded(123, 789)],
            )
            ->willReturn($this->envelope);

        $this->service->revisionsAdded($review, [$revisionA, $revisionB]);
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
                [new ReviewRevisionRemoved(123, 456)],
                [new ReviewRevisionRemoved(123, 789)],
            )
            ->willReturn($this->envelope);

        $this->service->revisionsDetached($review, [$revisionA, $revisionB]);
    }
}

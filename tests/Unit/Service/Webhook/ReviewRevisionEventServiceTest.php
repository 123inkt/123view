<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Webhook;

use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Message\Review\ReviewClosed;
use DR\Review\Message\Review\ReviewCreated;
use DR\Review\Message\Review\ReviewOpened;
use DR\Review\Message\Review\ReviewResumed;
use DR\Review\Message\Revision\ReviewRevisionAdded;
use DR\Review\Message\Revision\ReviewRevisionRemoved;
use DR\Review\Service\CodeReview\CodeReviewerStateResolver;
use DR\Review\Service\Webhook\ReviewRevisionEventService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(ReviewRevisionEventService::class)]
class ReviewRevisionEventServiceTest extends AbstractTestCase
{
    private CodeReviewerStateResolver&MockObject $reviewerStateResolver;
    private MessageBusInterface&MockObject       $bus;
    private ReviewRevisionEventService           $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->reviewerStateResolver = $this->createMock(CodeReviewerStateResolver::class);
        $this->bus                   = $this->createMock(MessageBusInterface::class);
        $this->service               = new ReviewRevisionEventService($this->reviewerStateResolver, $this->bus);
    }

    public function testRevisionAddedToReview(): void
    {
        $revision = new Revision();
        $revision->setId(456);
        $revision->setTitle('title');
        $review = new CodeReview();
        $review->setId(123);
        $review->setState(CodeReviewStateType::OPEN);

        $this->reviewerStateResolver->expects($this->once())->method('getReviewersState')->with($review)->willReturn(CodeReviewerStateType::OPEN);

        $this->bus->expects($this->exactly(4))
            ->method('dispatch')
            ->with(
                ...consecutive(
                    [new Envelope(new ReviewCreated(123, 456))],
                    [new Envelope(new ReviewOpened(123, null))],
                    [new Envelope(new ReviewResumed(123, null))],
                    [new Envelope(new ReviewRevisionAdded(123, 456, null, 'title'))],
                )
            )
            ->willReturn($this->envelope);

        $this->service->revisionAddedToReview($review, $revision, true, CodeReviewStateType::CLOSED, CodeReviewerStateType::ACCEPTED);
    }

    public function testRevisionAddedToReviewWithMinimalEvents(): void
    {
        $revision = new Revision();
        $revision->setId(456);
        $revision->setTitle('title');
        $review = new CodeReview();
        $review->setId(123);
        $review->setState(CodeReviewStateType::OPEN);

        $this->bus->expects($this->once())
            ->method('dispatch')
            ->with(new Envelope(new ReviewRevisionAdded(123, 456, null, 'title')))
            ->willReturn($this->envelope);

        $this->service->revisionAddedToReview($review, $revision, false, CodeReviewStateType::OPEN, CodeReviewerStateType::OPEN);
    }

    public function testRevisionRemovedFromReview(): void
    {
        $revision = new Revision();
        $revision->setId(456);
        $revision->setTitle('title');
        $review = new CodeReview();
        $review->setId(123);
        $review->setState(CodeReviewStateType::OPEN);

        $this->bus->expects($this->exactly(2))
            ->method('dispatch')
            ->with(
                ...consecutive(
                    [new Envelope(new ReviewRevisionRemoved(123, 456, null, 'title'))],
                    [new Envelope(new ReviewClosed(123, null))]
                )
            )
            ->willReturn($this->envelope);

        $this->service->revisionRemovedFromReview($review, $revision, CodeReviewStateType::CLOSED);
    }

    public function testRevisionRemovedFromReviewWithMinimalEvents(): void
    {
        $revision = new Revision();
        $revision->setId(456);
        $revision->setTitle('title');
        $review = new CodeReview();
        $review->setId(123);
        $review->setState(CodeReviewStateType::OPEN);

        $this->bus->expects($this->once())
            ->method('dispatch')
            ->with(new Envelope(new ReviewRevisionRemoved(123, 456, null, 'title')))
            ->willReturn($this->envelope);

        $this->service->revisionRemovedFromReview($review, $revision, CodeReviewStateType::OPEN);
    }
}

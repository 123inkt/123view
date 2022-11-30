<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\MessageHandler;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Doctrine\Type\CodeReviewerStateType;
use DR\GitCommitNotification\Doctrine\Type\CodeReviewStateType;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\CodeReviewer;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Message\Review\ReviewCreated;
use DR\GitCommitNotification\Message\Review\ReviewOpened;
use DR\GitCommitNotification\Message\Revision\NewRevisionMessage;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionAdded;
use DR\GitCommitNotification\MessageHandler\NewRevisionMessageHandler;
use DR\GitCommitNotification\Repository\Review\CodeReviewerRepository;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;
use DR\GitCommitNotification\Service\CodeReview\CodeReviewRevisionMatcher;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use RuntimeException;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

/**
 * @coversDefaultClass \DR\GitCommitNotification\MessageHandler\NewRevisionMessageHandler
 * @covers ::__construct
 */
class NewRevisionMessageHandlerTest extends AbstractTestCase
{
    private RevisionRepository&MockObject        $revisionRepository;
    private CodeReviewRepository&MockObject      $reviewRepository;
    private CodeReviewerRepository&MockObject    $reviewerRepository;
    private CodeReviewRevisionMatcher&MockObject $reviewRevisionMatcher;
    private ManagerRegistry&MockObject           $registry;
    private MessageBusInterface&MockObject       $bus;
    private NewRevisionMessageHandler            $messageHandler;
    private Envelope                             $envelope;

    public function setUp(): void
    {
        parent::setUp();
        $this->envelope              = new Envelope(new stdClass(), []);
        $this->revisionRepository    = $this->createMock(RevisionRepository::class);
        $this->reviewRepository      = $this->createMock(CodeReviewRepository::class);
        $this->reviewerRepository    = $this->createMock(CodeReviewerRepository::class);
        $this->reviewRevisionMatcher = $this->createMock(CodeReviewRevisionMatcher::class);
        $this->registry              = $this->createMock(ManagerRegistry::class);
        $this->bus                   = $this->createMock(MessageBusInterface::class);
        $this->messageHandler        = new NewRevisionMessageHandler(
            $this->revisionRepository,
            $this->reviewRepository,
            $this->reviewerRepository,
            $this->reviewRevisionMatcher,
            $this->registry,
            $this->bus
        );
        $this->messageHandler->setLogger($this->createMock(LoggerInterface::class));
    }

    /**
     * @covers ::__invoke
     * @throws Throwable
     */
    public function testInvokeShouldOnlyHandleSupportedRevisions(): void
    {
        $message  = new NewRevisionMessage(123);
        $revision = new Revision();

        $this->revisionRepository->expects(self::once())->method('find')->with(123)->willReturn($revision);
        $this->reviewRevisionMatcher->expects(self::once())->method('isSupported')->with($revision)->willReturn(false);
        $this->reviewRevisionMatcher->expects(self::never())->method('match');

        ($this->messageHandler)($message);
    }

    /**
     * @covers ::__invoke
     * @throws Throwable
     */
    public function testInvokeShouldOnlyHandleIfRevisionCanBeMatchedToReview(): void
    {
        $message  = new NewRevisionMessage(123);
        $revision = new Revision();

        $this->revisionRepository->expects(self::once())->method('find')->with(123)->willReturn($revision);
        $this->reviewRevisionMatcher->expects(self::once())->method('isSupported')->with($revision)->willReturn(true);
        $this->reviewRevisionMatcher->expects(self::once())->method('match')->with($revision)->willReturn(null);

        ($this->messageHandler)($message);
    }

    /**
     * @covers ::__invoke
     * @covers ::dispatchAfter
     * @throws Throwable
     */
    public function testInvokeShouldHandleNewReview(): void
    {
        $message  = new NewRevisionMessage(123);
        $revision = new Revision();
        $review   = new CodeReview();

        $this->revisionRepository->expects(self::once())->method('find')->with(123)->willReturn($revision);
        $this->reviewRevisionMatcher->expects(self::once())->method('isSupported')->with($revision)->willReturn(true);
        $this->reviewRevisionMatcher->expects(self::once())->method('match')->with($revision)->willReturn($review);
        $this->reviewRepository->expects(self::once())->method('save')->with($review, true);
        $this->bus->expects(self::exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [self::callback(static fn(Envelope $envelope) => $envelope->getMessage() instanceof ReviewCreated)],
                [self::callback(static fn(Envelope $envelope) => $envelope->getMessage() instanceof ReviewRevisionAdded)]
            )
            ->willReturn($this->envelope);

        ($this->messageHandler)($message);
    }

    /**
     * @covers ::__invoke
     * @covers ::dispatchAfter
     * @throws Throwable
     */
    public function testInvokeShouldHandleReopenClosedReview(): void
    {
        $message  = new NewRevisionMessage(123);
        $revision = new Revision();
        $reviewer = new CodeReviewer();
        $reviewer->setState(CodeReviewerStateType::ACCEPTED);
        $review = new CodeReview();
        $review->setId(123);
        $review->setState(CodeReviewStateType::CLOSED);
        $review->getReviewers()->add($reviewer);

        $this->revisionRepository->expects(self::once())->method('find')->with(123)->willReturn($revision);
        $this->reviewRevisionMatcher->expects(self::once())->method('isSupported')->with($revision)->willReturn(true);
        $this->reviewRevisionMatcher->expects(self::once())->method('match')->with($revision)->willReturn($review);
        $this->revisionRepository->expects(self::once())->method('save')->with($revision, true);
        $this->reviewRepository->expects(self::once())->method('save')->with($review, true);
        $this->reviewerRepository->expects(self::once())->method('save')->with($reviewer, true);
        $this->bus->expects(self::exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [self::callback(static fn(Envelope $envelope) => $envelope->getMessage() instanceof ReviewOpened)],
                [self::callback(static fn(Envelope $envelope) => $envelope->getMessage() instanceof ReviewRevisionAdded)]
            )
            ->willReturn($this->envelope);

        ($this->messageHandler)($message);

        // expect reviewer state to be opened again
        static::assertSame(CodeReviewerStateType::OPEN, $reviewer->getState());
    }

    /**
     * @covers ::__invoke
     * @covers ::dispatchAfter
     * @throws Throwable
     */
    public function testInvokeShouldResetRegistryManagerOnException(): void
    {
        $message  = new NewRevisionMessage(123);
        $revision = new Revision();
        $review   = new CodeReview();

        $this->revisionRepository->expects(self::once())->method('find')->with(123)->willReturn($revision);
        $this->reviewRevisionMatcher->expects(self::once())->method('isSupported')->with($revision)->willReturn(true);
        $this->reviewRevisionMatcher->expects(self::once())->method('match')->with($revision)->willReturn($review);
        $this->reviewRepository->expects(self::once())->method('save')->with($review, true)->willThrowException(new RuntimeException());
        $this->registry->expects(self::once())->method('resetManager');

        $this->expectException(RuntimeException::class);
        ($this->messageHandler)($message);
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\Review\Revision;
use DR\Review\Message\Revision\NewRevisionMessage;
use DR\Review\MessageHandler\NewRevisionMessageHandler;
use DR\Review\Repository\Review\RevisionRepository;
use DR\Review\Service\CodeReview\CodeReviewRevisionMatcher;
use DR\Review\Service\CodeReview\FileSeenStatusService;
use DR\Review\Service\Git\Review\CodeReviewService;
use DR\Review\Service\Webhook\ReviewEventService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

/**
 * @coversDefaultClass \DR\Review\MessageHandler\NewRevisionMessageHandler
 * @covers ::__construct
 * @suppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NewRevisionMessageHandlerTest extends AbstractTestCase
{
    private RevisionRepository&MockObject        $revisionRepository;
    private CodeReviewService&MockObject         $reviewService;
    private FileSeenStatusService&MockObject     $seenStatusService;
    private CodeReviewRevisionMatcher&MockObject $reviewRevisionMatcher;
    private ManagerRegistry&MockObject           $registry;
    private ReviewEventService&MockObject        $eventService;
    private NewRevisionMessageHandler            $messageHandler;

    public function setUp(): void
    {
        parent::setUp();
        $this->revisionRepository    = $this->createMock(RevisionRepository::class);
        $this->reviewService         = $this->createMock(CodeReviewService::class);
        $this->seenStatusService     = $this->createMock(FileSeenStatusService::class);
        $this->reviewRevisionMatcher = $this->createMock(CodeReviewRevisionMatcher::class);
        $this->registry              = $this->createMock(ManagerRegistry::class);
        $this->eventService          = $this->createMock(ReviewEventService::class);
        $this->messageHandler        = new NewRevisionMessageHandler(
            $this->revisionRepository,
            $this->reviewService,
            $this->reviewRevisionMatcher,
            $this->seenStatusService,
            $this->registry,
            $this->eventService
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
        $this->reviewService->expects(self::once())->method('addRevisions')->with($review, [$revision]);
        $this->eventService->expects(self::once())
            ->method('revisionAddedToReview')
            ->with($review, $revision, true, CodeReviewStateType::OPEN, CodeReviewerStateType::OPEN);

        ($this->messageHandler)($message);
    }

    /**
     * @covers ::__invoke
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
        $this->reviewService->expects(self::once())
            ->method('addRevisions')
            ->with(
                static::callback(
                    static function (CodeReview $review) use ($reviewer) {
                        $review->setState(CodeReviewStateType::OPEN);
                        $reviewer->setState(CodeReviewerStateType::OPEN);

                        return true;
                    }
                ),
                [$revision]
            );
        $this->seenStatusService->expects(self::once())->method('markAllAsUnseen')->with($review, $revision);
        $this->eventService->expects(self::once())
            ->method('revisionAddedToReview')
            ->with($review, $revision, false, CodeReviewStateType::CLOSED, CodeReviewerStateType::ACCEPTED);

        ($this->messageHandler)($message);

        // expect reviewer state to be opened again
        static::assertSame(CodeReviewerStateType::OPEN, $reviewer->getState());
    }

    /**
     * @covers ::__invoke
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
        $this->reviewService->expects(self::once())->method('addRevisions')->with($review, [$revision])->willThrowException(new RuntimeException());
        $this->registry->expects(self::once())->method('resetManager');

        $this->expectException(RuntimeException::class);
        ($this->messageHandler)($message);
    }
}

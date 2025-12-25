<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Message\Revision\NewRevisionMessage;
use DR\Review\MessageHandler\NewRevisionMessageHandler;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\CodeReview\CodeReviewerStateResolver;
use DR\Review\Service\CodeReview\CodeReviewRevisionMatcher;
use DR\Review\Service\CodeReview\FileSeenStatusService;
use DR\Review\Service\Git\Review\CodeReviewService;
use DR\Review\Service\Webhook\ReviewRevisionEventService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

#[CoversClass(NewRevisionMessageHandler::class)]
class NewRevisionMessageHandlerTest extends AbstractTestCase
{
    private RevisionRepository&MockObject         $revisionRepository;
    private CodeReviewService&MockObject          $reviewService;
    private CodeReviewerStateResolver&MockObject  $reviewerStateResolver;
    private FileSeenStatusService&MockObject      $seenStatusService;
    private CodeReviewRevisionMatcher&MockObject  $reviewRevisionMatcher;
    private ManagerRegistry&MockObject            $registry;
    private ReviewRevisionEventService&MockObject $eventService;
    private NewRevisionMessageHandler             $messageHandler;

    public function setUp(): void
    {
        parent::setUp();
        $this->revisionRepository    = $this->createMock(RevisionRepository::class);
        $this->reviewService         = $this->createMock(CodeReviewService::class);
        $this->reviewerStateResolver = $this->createMock(CodeReviewerStateResolver::class);
        $this->seenStatusService     = $this->createMock(FileSeenStatusService::class);
        $this->reviewRevisionMatcher = $this->createMock(CodeReviewRevisionMatcher::class);
        $this->registry              = $this->createMock(ManagerRegistry::class);
        $this->eventService          = $this->createMock(ReviewRevisionEventService::class);
        $this->messageHandler        = new NewRevisionMessageHandler(
            $this->revisionRepository,
            $this->reviewService,
            $this->reviewerStateResolver,
            $this->reviewRevisionMatcher,
            $this->seenStatusService,
            $this->registry,
            $this->eventService
        );
        $this->messageHandler->setLogger($this->createMock(LoggerInterface::class));
    }

    /**
     * @throws Throwable
     */
    public function testInvokeShouldOnlyHandleSupportedRevisions(): void
    {
        $message  = new NewRevisionMessage(123);
        $revision = new Revision();

        $this->revisionRepository->expects($this->once())->method('find')->with(123)->willReturn($revision);
        $this->reviewRevisionMatcher->expects($this->once())->method('isSupported')->with($revision)->willReturn(false);
        $this->reviewRevisionMatcher->expects($this->never())->method('match');

        ($this->messageHandler)($message);
    }

    /**
     * @throws Throwable
     */
    public function testInvokeShouldOnlyHandleIfRevisionCanBeMatchedToReview(): void
    {
        $message  = new NewRevisionMessage(123);
        $revision = new Revision();
        $revision->setTitle('title');

        $this->revisionRepository->expects($this->once())->method('find')->with(123)->willReturn($revision);
        $this->reviewRevisionMatcher->expects($this->once())->method('isSupported')->with($revision)->willReturn(true);
        $this->reviewRevisionMatcher->expects($this->once())->method('match')->with($revision)->willReturn(null);

        ($this->messageHandler)($message);
    }

    /**
     * @throws Throwable
     */
    public function testInvokeShouldHandleNewReview(): void
    {
        $message  = new NewRevisionMessage(123);
        $revision = new Revision();
        $revision->setTitle('title');
        $revision->setCommitHash('hash');
        $review = new CodeReview();

        $this->revisionRepository->expects($this->once())->method('find')->with(123)->willReturn($revision);
        $this->reviewerStateResolver->expects($this->once())->method('getReviewersState')->with($review)->willReturn(CodeReviewerStateType::OPEN);
        $this->reviewRevisionMatcher->expects($this->once())->method('isSupported')->with($revision)->willReturn(true);
        $this->reviewRevisionMatcher->expects($this->once())->method('match')->with($revision)->willReturn($review);
        $this->reviewService->expects($this->once())->method('addRevisions')->with($review, [$revision]);
        $this->eventService->expects($this->once())
            ->method('revisionAddedToReview')
            ->with($review, $revision, true, CodeReviewStateType::OPEN, CodeReviewerStateType::OPEN);

        ($this->messageHandler)($message);
    }

    /**
     * @throws Throwable
     */
    public function testInvokeShouldHandleReopenClosedReview(): void
    {
        $message  = new NewRevisionMessage(123);
        $revision = new Revision();
        $revision->setTitle('title');
        $revision->setCommitHash('hash');
        $reviewer = new CodeReviewer();
        $reviewer->setState(CodeReviewerStateType::ACCEPTED);
        $review = new CodeReview();
        $review->setId(123);
        $review->setState(CodeReviewStateType::CLOSED);
        $review->getReviewers()->add($reviewer);

        $this->revisionRepository->expects($this->once())->method('find')->with(123)->willReturn($revision);
        $this->reviewerStateResolver->expects($this->once())->method('getReviewersState')->with($review)->willReturn(CodeReviewerStateType::ACCEPTED);
        $this->reviewRevisionMatcher->expects($this->once())->method('isSupported')->with($revision)->willReturn(true);
        $this->reviewRevisionMatcher->expects($this->once())->method('match')->with($revision)->willReturn($review);
        $this->reviewService->expects($this->once())
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
        $this->seenStatusService->expects($this->once())->method('markAllAsUnseen')->with($review, $revision);
        $this->eventService->expects($this->once())
            ->method('revisionAddedToReview')
            ->with($review, $revision, false, CodeReviewStateType::CLOSED, CodeReviewerStateType::ACCEPTED);

        ($this->messageHandler)($message);

        // expect reviewer state to be opened again
        static::assertSame(CodeReviewerStateType::OPEN, $reviewer->getState());
    }

    /**
     * @throws Throwable
     */
    public function testInvokeShouldResetRegistryManagerOnException(): void
    {
        $message  = new NewRevisionMessage(123);
        $revision = new Revision();
        $review   = new CodeReview();

        $this->revisionRepository->expects($this->once())->method('find')->with(123)->willReturn($revision);
        $this->reviewRevisionMatcher->expects($this->once())->method('isSupported')->with($revision)->willReturn(true);
        $this->reviewRevisionMatcher->expects($this->once())->method('match')->with($revision)->willReturn($review);
        $this->reviewService->expects($this->once())->method('addRevisions')->with($review, [$revision])->willThrowException(new RuntimeException());
        $this->registry->expects($this->once())->method('resetManager');

        $this->expectException(RuntimeException::class);
        ($this->messageHandler)($message);
    }
}

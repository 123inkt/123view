<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler;

use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Message\Revision\NewRevisionMessage;
use DR\Review\MessageHandler\NewRevisionBranchReviewMessageHandler;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\CodeReview\CodeReviewerStateResolver;
use DR\Review\Service\CodeReview\FileSeenStatusService;
use DR\Review\Service\Git\Review\CodeReviewService;
use DR\Review\Service\Webhook\ReviewRevisionEventService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(NewRevisionBranchReviewMessageHandler::class)]
class NewRevisionBranchReviewMessageHandlerTest extends AbstractTestCase
{
    private RevisionRepository&MockObject         $revisionRepository;
    private CodeReviewRepository&MockObject       $reviewRepository;
    private CodeReviewService&MockObject          $reviewService;
    private CodeReviewerStateResolver&MockObject  $reviewerStateResolver;
    private FileSeenStatusService&MockObject      $seenStatusService;
    private ReviewRevisionEventService&MockObject $eventService;
    private NewRevisionBranchReviewMessageHandler $messageHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->revisionRepository    = $this->createMock(RevisionRepository::class);
        $this->reviewRepository      = $this->createMock(CodeReviewRepository::class);
        $this->reviewService         = $this->createMock(CodeReviewService::class);
        $this->reviewerStateResolver = $this->createMock(CodeReviewerStateResolver::class);
        $this->seenStatusService     = $this->createMock(FileSeenStatusService::class);
        $this->eventService          = $this->createMock(ReviewRevisionEventService::class);
        $this->messageHandler        = new NewRevisionBranchReviewMessageHandler(
            $this->revisionRepository,
            $this->reviewRepository,
            $this->reviewService,
            $this->reviewerStateResolver,
            $this->seenStatusService,
            $this->eventService
        );
    }

    /**
     * @throws Throwable
     */
    public function testInvokeUnknownRevision(): void
    {
        $this->revisionRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->reviewRepository->expects($this->never())->method('findOneBy');
        $this->reviewService->expects($this->never())->method('addRevisions');
        $this->reviewerStateResolver->expects($this->never())->method('getReviewersState');
        $this->seenStatusService->expects($this->never())->method('markAllAsUnseen');
        $this->eventService->expects($this->never())->method('revisionAddedToReview');
        ($this->messageHandler)(new NewRevisionMessage(123));
    }

    /**
     * @throws Throwable
     */
    public function testInvokeUnknownReview(): void
    {
        $repository = new Repository();
        $revision   = (new Revision())->setRepository($repository)->setFirstBranch('first-branch');

        $this->revisionRepository->expects($this->once())->method('find')->with(123)->willReturn($revision);
        $this->reviewRepository->expects($this->once())->method('findOneBy')
            ->with(
                [
                    'referenceId' => ['first-branch', 'origin/first-branch'],
                    'type'        => CodeReviewType::BRANCH,
                    'repository'  => $repository
                ]
            )
            ->willReturn(null);

        $this->reviewerStateResolver->expects($this->never())->method('getReviewersState');
        $this->reviewService->expects($this->never())->method('addRevisions');
        $this->seenStatusService->expects($this->never())->method('markAllAsUnseen');
        $this->eventService->expects($this->never())->method('revisionAddedToReview');

        ($this->messageHandler)(new NewRevisionMessage(123));
    }

    /**
     * @throws Throwable
     */
    public function testInvoke(): void
    {
        $repository = new Repository();
        $revision   = (new Revision())->setRepository($repository)->setFirstBranch('first-branch');
        $review     = (new CodeReview())->setState(CodeReviewStateType::OPEN);

        $this->revisionRepository->expects($this->once())->method('find')->with(123)->willReturn($revision);
        $this->reviewRepository->expects($this->once())->method('findOneBy')->willReturn($review);
        $this->reviewerStateResolver->expects($this->once())->method('getReviewersState')->with($review)->willReturn(CodeReviewerStateType::ACCEPTED);
        $this->reviewService->expects($this->once())->method('addRevisions')->with($review, [$revision]);
        $this->seenStatusService->expects($this->once())->method('markAllAsUnseen')->with($review, $revision);
        $this->eventService->expects($this->once())
            ->method('revisionAddedToReview')
            ->with($review, $revision, false, CodeReviewStateType::OPEN, CodeReviewerStateType::ACCEPTED);

        ($this->messageHandler)(new NewRevisionMessage(123));
    }
}

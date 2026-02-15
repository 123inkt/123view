<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler\Gitlab;

use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Repository\RepositoryProperty;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Message\Reviewer\ReviewerStateChanged;
use DR\Review\MessageHandler\Gitlab\ReviewerStateChangeMessageHandler;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Api\Gitlab\ReviewApprovalService;
use DR\Review\Service\Api\Gitlab\ReviewApprovalValidatorService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(ReviewerStateChangeMessageHandler::class)]
class ReviewerStateChangeMessageHandlerTest extends AbstractTestCase
{
    private CodeReviewRepository&MockObject              $reviewRepository;
    private ReviewApprovalValidatorService&MockObject    $reviewApprovalValidatorService;
    private ReviewApprovalService&MockObject             $reviewApprovalService;
    private ReviewerStateChangeMessageHandler            $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reviewRepository               = $this->createMock(CodeReviewRepository::class);
        $this->reviewApprovalValidatorService = $this->createMock(ReviewApprovalValidatorService::class);
        $this->reviewApprovalService          = $this->createMock(ReviewApprovalService::class);
        $this->handler                        = new ReviewerStateChangeMessageHandler(
            true,
            $this->reviewRepository,
            $this->reviewApprovalValidatorService,
            $this->reviewApprovalService
        );
    }

    /**
     * @throws Throwable
     */
    public function testInvokeShouldSkipWhenDisabled(): void
    {
        $handler = new ReviewerStateChangeMessageHandler(
            false,
            $this->reviewRepository,
            $this->reviewApprovalValidatorService,
            $this->reviewApprovalService
        );
        $this->reviewRepository->expects($this->never())->method('find');
        ($handler)(new ReviewerStateChanged(123, 456, 789, 'foo', 'bar'));
    }

    /**
     * @throws Throwable
     */
    public function testInvokeShouldSkipReviewIsAbsent(): void
    {
        $this->reviewRepository->expects($this->once())->method('find')->willReturn(null);
        $this->reviewApprovalValidatorService->expects($this->once())->method('validate')->willReturn(false);
        $this->reviewApprovalService->expects($this->never())->method('approve');
        ($this->handler)(new ReviewerStateChanged(123, 456, 789, 'foo', 'bar'));
    }

    /**
     * @throws Throwable
     */
    public function testInvokeShouldSkipWhenValidationFails(): void
    {
        $repository = new Repository();
        $repository->getRepositoryProperties()->set('gitlab-project-id', new RepositoryProperty('gitlab-project-id', '666'));
        $reviewer = (new CodeReviewer())->setId(456);
        $review   = (new CodeReview())->setId(123);
        $review->getReviewers()->add($reviewer);
        $review->setRepository($repository);

        $this->reviewRepository->expects($this->once())->method('find')->willReturn($review);
        $this->reviewApprovalValidatorService->expects($this->once())->method('validate')->willReturn(false);
        $this->reviewApprovalService->expects($this->never())->method('approve');
        ($this->handler)(new ReviewerStateChanged(123, 456, 789, 'foo', 'bar'));
    }

    /**
     * @throws Throwable
     */
    public function testInvokeShouldApprove(): void
    {
        $repository = new Repository();
        $repository->getRepositoryProperties()->set('gitlab-project-id', new RepositoryProperty('gitlab-project-id', '666'));
        $reviewer = (new CodeReviewer())->setId(456);
        $review   = (new CodeReview())->setId(123);
        $review->getReviewers()->add($reviewer);
        $review->setRepository($repository);

        $this->reviewRepository->expects($this->once())->method('find')->willReturn($review);
        $this->reviewApprovalValidatorService->expects($this->once())->method('validate')->willReturn(true);
        $this->reviewApprovalService->expects($this->once())->method('approve')->with($review, $reviewer, true);
        ($this->handler)(new ReviewerStateChanged(123, 456, 789, 'foo', CodeReviewerStateType::ACCEPTED));
    }

    /**
     * @throws Throwable
     */
    public function testInvokeShouldUnapprove(): void
    {
        $repository = new Repository();
        $repository->getRepositoryProperties()->set('gitlab-project-id', new RepositoryProperty('gitlab-project-id', '666'));
        $reviewer = (new CodeReviewer())->setId(456);
        $review   = (new CodeReview())->setId(123);
        $review->getReviewers()->add($reviewer);
        $review->setRepository($repository);

        $this->reviewRepository->expects($this->once())->method('find')->willReturn($review);
        $this->reviewApprovalValidatorService->expects($this->once())->method('validate')->willReturn(true);
        $this->reviewApprovalService->expects($this->once())->method('approve')->with($review, $reviewer, false);
        ($this->handler)(new ReviewerStateChanged(123, 456, 789, CodeReviewerStateType::ACCEPTED, 'bar'));
    }
}

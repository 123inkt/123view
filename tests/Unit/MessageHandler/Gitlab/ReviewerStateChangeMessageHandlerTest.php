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
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(ReviewerStateChangeMessageHandler::class)]
class ReviewerStateChangeMessageHandlerTest extends AbstractTestCase
{
    private CodeReviewRepository&MockObject   $reviewRepository;
    private ReviewApprovalService&MockObject  $reviewApprovalService;
    private ReviewerStateChangeMessageHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reviewRepository      = $this->createMock(CodeReviewRepository::class);
        $this->reviewApprovalService = $this->createMock(ReviewApprovalService::class);
        $this->handler               = new ReviewerStateChangeMessageHandler(
            true,
            '/^PR-[0-9]+/',
            $this->reviewRepository,
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
            '/^PR-[0-9]+/',
            $this->reviewRepository,
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
        $this->reviewApprovalService->expects($this->never())->method('approve');
        ($this->handler)(new ReviewerStateChanged(123, 456, 789, 'foo', 'bar'));
    }

    /**
     * @throws Throwable
     */
    public function testInvokeShouldSkipIfRemoteRefDoesNotMatch(): void
    {
        $revision   = (new Revision())->setFirstBranch('PR-FOOBAR');
        $repository = new Repository();
        $repository->getRepositoryProperties()->set('gitlab-project-id', new RepositoryProperty('gitlab-project-id', '666'));
        $reviewer = (new CodeReviewer())->setId(456);
        $review   = (new CodeReview())->setId(123);
        $review->getReviewers()->add($reviewer);
        $review->setRepository($repository);
        $review->getRevisions()->add($revision);

        $this->reviewRepository->expects($this->once())->method('find')->willReturn($review);
        $this->reviewApprovalService->expects($this->never())->method('approve');
        ($this->handler)(new ReviewerStateChanged(123, 456, 789, 'foo', 'bar'));
    }

    /**
     * @throws Throwable
     */
    public function testInvokeShouldApprove(): void
    {
        $revision   = (new Revision())->setFirstBranch('PR-12345');
        $repository = new Repository();
        $repository->getRepositoryProperties()->set('gitlab-project-id', new RepositoryProperty('gitlab-project-id', '666'));
        $reviewer = (new CodeReviewer())->setId(456);
        $review   = (new CodeReview())->setId(123);
        $review->getReviewers()->add($reviewer);
        $review->setRepository($repository);
        $review->getRevisions()->add($revision);

        $this->reviewRepository->expects($this->once())->method('find')->willReturn($review);
        $this->reviewApprovalService->expects($this->once())->method('approve')->with($review, $reviewer, true);
        ($this->handler)(new ReviewerStateChanged(123, 456, 789, 'foo', CodeReviewerStateType::ACCEPTED));
    }

    /**
     * @throws Throwable
     */
    public function testInvokeShouldUnapprove(): void
    {
        $revision   = (new Revision())->setFirstBranch('PR-12345');
        $repository = new Repository();
        $repository->getRepositoryProperties()->set('gitlab-project-id', new RepositoryProperty('gitlab-project-id', '666'));
        $reviewer = (new CodeReviewer())->setId(456);
        $review   = (new CodeReview())->setId(123);
        $review->getReviewers()->add($reviewer);
        $review->setRepository($repository);
        $review->getRevisions()->add($revision);

        $this->reviewRepository->expects($this->once())->method('find')->willReturn($review);
        $this->reviewApprovalService->expects($this->once())->method('approve')->with($review, $reviewer, false);
        ($this->handler)(new ReviewerStateChanged(123, 456, 789, CodeReviewerStateType::ACCEPTED, 'bar'));
    }
}

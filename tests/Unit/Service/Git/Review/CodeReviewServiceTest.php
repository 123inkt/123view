<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Review;

use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CodeReviewerRepository;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\Git\Review\CodeReviewService;
use DR\Review\Service\Revision\RevisionVisibilityService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CodeReviewService::class)]
class CodeReviewServiceTest extends AbstractTestCase
{
    private RevisionRepository&MockObject        $revisionRepository;
    private CodeReviewRepository&MockObject      $reviewRepository;
    private CodeReviewRevisionService&MockObject $revisionService;
    private CodeReviewerRepository&MockObject    $reviewerRepository;
    private RevisionVisibilityService&MockObject $visibilityService;
    private CodeReviewService                    $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->revisionRepository = $this->createMock(RevisionRepository::class);
        $this->reviewRepository   = $this->createMock(CodeReviewRepository::class);
        $this->revisionService    = $this->createMock(CodeReviewRevisionService::class);
        $this->reviewerRepository = $this->createMock(CodeReviewerRepository::class);
        $this->visibilityService  = $this->createMock(RevisionVisibilityService::class);
        $this->service            = new CodeReviewService(
            $this->revisionRepository,
            $this->reviewRepository,
            $this->revisionService,
            $this->reviewerRepository,
            $this->visibilityService
        );
    }

    public function testAddRevisionsAndPersist(): void
    {
        $revisionA = new Revision();
        $revisionB = new Revision();
        $user      = new User();
        $reviewer  = new CodeReviewer();
        $reviewer->setUser($user);
        $reviewer->setState(CodeReviewerStateType::ACCEPTED);
        $review = new CodeReview();
        $review->setState(CodeReviewStateType::CLOSED);
        $review->getReviewers()->add($reviewer);

        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([$revisionA]);
        $this->revisionRepository->expects($this->once())->method('save')->with($revisionB, true);
        $this->reviewRepository->expects($this->once())->method('save')->with($review, true);
        $this->reviewerRepository->expects($this->once())->method('save')->with($reviewer, true);
        $this->visibilityService->expects($this->once())->method('setRevisionVisibility')->with($review, [$revisionA], $user, false);

        $this->service->addRevisions($review, [$revisionB]);

        static::assertSame($review, $revisionB->getReview());
        static::assertTrue($review->getRevisions()->contains($revisionB));
        static::assertSame(CodeReviewStateType::OPEN, $review->getState());
        static::assertSame(CodeReviewerStateType::OPEN, $reviewer->getState());
    }

    public function testAddRevisionsShouldSkipReviewers(): void
    {
        $revision = new Revision();
        $user     = new User();
        $reviewer = new CodeReviewer();
        $reviewer->setUser($user);
        $reviewer->setState(CodeReviewerStateType::OPEN);
        $review = new CodeReview();
        $review->setState(CodeReviewStateType::CLOSED);
        $review->getReviewers()->add($reviewer);

        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([]);
        $this->revisionRepository->expects($this->once())->method('save')->with($revision, true);
        $this->reviewRepository->expects($this->once())->method('save')->with($review, true);
        $this->reviewerRepository->expects($this->never())->method('save');
        $this->visibilityService->expects($this->never())->method('setRevisionVisibility');

        $this->service->addRevisions($review, [$revision]);
    }
}

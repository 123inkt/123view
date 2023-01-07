<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Review;

use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Repository\Review\CodeReviewerRepository;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Review\RevisionRepository;
use DR\Review\Service\Git\Review\CodeReviewService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Service\Git\Review\CodeReviewService
 * @covers ::__construct
 */
class CodeReviewServiceTest extends AbstractTestCase
{
    private RevisionRepository&MockObject     $revisionRepository;
    private CodeReviewRepository&MockObject   $reviewRepository;
    private CodeReviewerRepository&MockObject $reviewerRepository;
    private CodeReviewService                 $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->revisionRepository = $this->createMock(RevisionRepository::class);
        $this->reviewRepository   = $this->createMock(CodeReviewRepository::class);
        $this->reviewerRepository = $this->createMock(CodeReviewerRepository::class);
        $this->service            = new CodeReviewService($this->revisionRepository, $this->reviewRepository, $this->reviewerRepository);
    }

    /**
     * @covers ::addRevisions
     */
    public function testAddRevisionsAndPersist(): void
    {
        $revision = new Revision();
        $reviewer = new CodeReviewer();
        $reviewer->setState(CodeReviewerStateType::ACCEPTED);
        $review = new CodeReview();
        $review->setState(CodeReviewStateType::CLOSED);
        $review->getReviewers()->add($reviewer);

        $this->revisionRepository->expects(self::once())->method('save')->with($revision, true);
        $this->reviewRepository->expects(self::once())->method('save')->with($review, true);
        $this->reviewerRepository->expects(self::once())->method('save')->with($reviewer, true);

        $this->service->addRevisions($review, [$revision]);

        static::assertSame($review, $revision->getReview());
        static::assertTrue($review->getRevisions()->contains($revision));
        static::assertSame(CodeReviewStateType::OPEN, $review->getState());
        static::assertSame(CodeReviewerStateType::OPEN, $reviewer->getState());
    }
}

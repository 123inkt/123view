<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\Review;

use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;
use DR\GitCommitNotification\Service\Git\Review\CodeReviewService;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\Review\CodeReviewService
 * @covers ::__construct
 */
class CodeReviewServiceTest extends AbstractTestCase
{
    private RevisionRepository&MockObject $revisionRepository;
    private CodeReviewService             $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->revisionRepository = $this->createMock(RevisionRepository::class);
        $this->service            = new CodeReviewService($this->revisionRepository);
    }

    /**
     * @covers ::addRevisions
     */
    public function testAddRevisionsAndPersist(): void
    {
        $revision = new Revision();
        $review   = new CodeReview();

        $this->revisionRepository->expects(self::once())->method('save')->with($revision);

        $this->service->addRevisions($review, [$revision], true);

        static::assertSame($review, $revision->getReview());
        static::assertTrue($review->getRevisions()->contains($revision));
    }

    /**
     * @covers ::addRevisions
     */
    public function testAddRevisionsWithoutPersist(): void
    {
        $revision = new Revision();
        $review   = new CodeReview();

        $this->revisionRepository->expects(self::never())->method('save');

        $this->service->addRevisions($review, [$revision], false);

        static::assertSame($review, $revision->getReview());
        static::assertTrue($review->getRevisions()->contains($revision));
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Service\CodeReview\CodeReviewDiffService;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(CodeReviewDiffService::class)]
class CodeReviewDiffServiceTest extends AbstractTestCase
{
    private ReviewDiffServiceInterface&MockObject $diffService;
    private CodeReviewRevisionService&MockObject  $revisionService;
    private CodeReviewDiffService                 $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->diffService     = $this->createMock(ReviewDiffServiceInterface::class);
        $this->revisionService = $this->createMock(CodeReviewRevisionService::class);
        $this->service         = new CodeReviewDiffService($this->diffService, $this->revisionService);
    }

    /**
     * @throws Throwable
     */
    public function testGetDiffForBranchReview(): void
    {
        $diffFile = new DiffFile();
        $review   = new CodeReview();
        $review->setType(CodeReviewType::BRANCH);
        $review->setReferenceId('main');

        $expectedOptions = new FileDiffOptions(5, DiffComparePolicy::IGNORE_EMPTY_LINES, includeRaw: true);

        $this->revisionService->expects($this->never())->method('getRevisions');
        $this->diffService->expects($this->once())
            ->method('getDiffForBranch')
            ->with($review, [], 'main', $expectedOptions)
            ->willReturn([$diffFile]);

        $result = $this->service->getDiff($review);
        static::assertSame([$diffFile], $result);
    }

    /**
     * @throws Throwable
     */
    public function testGetDiffForCommitsReview(): void
    {
        $repository = new Repository();
        $revision   = new Revision();
        $diffFile   = new DiffFile();

        $review = new CodeReview();
        $review->setType(CodeReviewType::COMMITS);
        $review->setRepository($repository);

        $expectedOptions = new FileDiffOptions(5, DiffComparePolicy::IGNORE_EMPTY_LINES, includeRaw: true);

        $this->revisionService->expects($this->once())
            ->method('getRevisions')
            ->with($review)
            ->willReturn([$revision]);

        $this->diffService->expects($this->never())->method('getDiffForBranch');
        $this->diffService->expects($this->once())
            ->method('getDiffForRevisions')
            ->with($repository, [$revision], $expectedOptions)
            ->willReturn([$diffFile]);

        $result = $this->service->getDiff($review);
        static::assertSame([$diffFile], $result);
    }
}

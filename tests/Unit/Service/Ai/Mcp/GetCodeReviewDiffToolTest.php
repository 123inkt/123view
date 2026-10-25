<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai\Mcp;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Exception\Ai\CodeReviewNotFoundException;
use DR\Review\Repository\Mcp\CodeReviewRepository;
use DR\Review\Service\Ai\Mcp\GetCodeReviewDiffTool;
use DR\Review\Service\CodeReview\CodeReviewDiffService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(GetCodeReviewDiffTool::class)]
class GetCodeReviewDiffToolTest extends AbstractTestCase
{
    private CodeReviewRepository&MockObject $reviewRepository;
    private CodeReviewDiffService&MockObject $diffService;
    private GetCodeReviewDiffTool $tool;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reviewRepository = $this->createMock(CodeReviewRepository::class);
        $this->diffService      = $this->createMock(CodeReviewDiffService::class);
        $this->tool             = new GetCodeReviewDiffTool($this->reviewRepository, $this->diffService);
    }

    /**
     * @throws Throwable
     */
    public function testInvokeThrowsWhenReviewNotFound(): void
    {
        $this->reviewRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->diffService->expects($this->never())->method('getDiff');

        $this->expectException(CodeReviewNotFoundException::class);
        ($this->tool)(123);
    }

    /**
     * @throws Throwable
     */
    public function testInvokeReturnsNoChangesMessageWhenDiffIsEmpty(): void
    {
        $review = new CodeReview();

        $this->reviewRepository->expects($this->once())->method('find')->with(456)->willReturn($review);
        $this->diffService->expects($this->once())->method('getDiff')->with($review)->willReturn([]);

        $result = ($this->tool)(456);
        static::assertSame('No changes found in this review.', $result);
    }

    /**
     * @throws Throwable
     */
    public function testInvokeReturnsConcatenatedRawDiff(): void
    {
        $review = new CodeReview();

        $fileA      = new DiffFile();
        $fileA->raw = 'diff --git a/foo.php';

        $fileB      = new DiffFile();
        $fileB->raw = 'diff --git a/bar.php';

        $this->reviewRepository->expects($this->once())->method('find')->with(789)->willReturn($review);
        $this->diffService->expects($this->once())->method('getDiff')->with($review)->willReturn([$fileA, $fileB]);

        $result = ($this->tool)(789);
        static::assertSame("diff --git a/foo.php\ndiff --git a/bar.php", $result);
    }
}

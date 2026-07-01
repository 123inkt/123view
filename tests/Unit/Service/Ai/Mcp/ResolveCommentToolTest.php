<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai\Mcp;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Exception\Ai\CodeReviewNotFoundException;
use DR\Review\Repository\Mcp\CodeReviewRepository;
use DR\Review\Service\Ai\Mcp\ResolveCommentTool;
use DR\Review\Service\CodeReview\Comment\ResolveCommentService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(ResolveCommentTool::class)]
class ResolveCommentToolTest extends AbstractTestCase
{
    private CodeReviewRepository&MockObject  $reviewRepository;
    private ResolveCommentService&MockObject $resolveCommentService;
    private ResolveCommentTool               $tool;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reviewRepository      = $this->createMock(CodeReviewRepository::class);
        $this->resolveCommentService = $this->createMock(ResolveCommentService::class);
        $this->tool                  = new ResolveCommentTool($this->reviewRepository, $this->resolveCommentService);
    }

    /**
     * @throws Throwable
     */
    public function testInvokeThrowsWhenReviewNotFound(): void
    {
        $this->reviewRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->resolveCommentService->expects($this->never())->method('resolve');

        $this->expectException(CodeReviewNotFoundException::class);
        ($this->tool)(123, 456);
    }

    /**
     * @throws Throwable
     */
    public function testInvokeShouldResolveComment(): void
    {
        $review = new CodeReview();

        $this->reviewRepository->expects($this->once())->method('find')->with(123)->willReturn($review);
        $this->resolveCommentService->expects($this->once())->method('resolve')->with(456, 123)->willReturn('Comment 456 resolved.');

        $result = ($this->tool)(123, 456);
        static::assertSame('Comment 456 resolved.', $result);
    }
}

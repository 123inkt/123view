<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai\Tool;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Ai\Tool\CodeReviewFileTool;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\Git\Show\LockableGitShowService;
use DR\Review\Tests\AbstractTestCase;
use Mcp\Exception\ToolCallException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CodeReviewFileTool::class)]
class CodeReviewFileToolTest extends AbstractTestCase
{
    private CodeReviewRepository&MockObject       $repository;
    private CodeReviewRevisionService&MockObject  $revisionService;
    private LockableGitShowService&MockObject     $gitShowService;
    private CodeReviewFileTool                    $tool;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository      = $this->createMock(CodeReviewRepository::class);
        $this->revisionService = $this->createMock(CodeReviewRevisionService::class);
        $this->gitShowService  = $this->createMock(LockableGitShowService::class);
        $this->tool            = new CodeReviewFileTool($this->logger, $this->repository, $this->revisionService, $this->gitShowService);
    }

    public function testInvokeShouldThrowExceptionWhenReviewNotFound(): void
    {
        $this->repository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->revisionService->expects($this->never())->method('getRevisions');
        $this->gitShowService->expects($this->never())->method('getFileContents');

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('Review not found: 123');
        ($this->tool)(123, 'path/to/file.php');
    }

    public function testInvokeShouldThrowExceptionWhenNoRevisions(): void
    {
        $review = new CodeReview();
        $this->repository->expects($this->once())->method('find')->with(123)->willReturn($review);
        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([]);
        $this->gitShowService->expects($this->never())->method('getFileContents');

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('No revisions for review: 123');
        ($this->tool)(123, 'path/to/file.php');
    }

    public function testInvokeShouldReturnFileContents(): void
    {
        $revision = new Revision();
        $review   = new CodeReview();

        $this->repository->expects($this->once())->method('find')->with(123)->willReturn($review);
        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->gitShowService->expects($this->once())
            ->method('getFileContents')
            ->with($revision, 'path/to/file.php')
            ->willReturn('file contents');

        $result = ($this->tool)(123, 'path/to/file.php');
        static::assertSame('file contents', $result);
    }

    public function testInvokeShouldUseLastRevision(): void
    {
        $revision1 = new Revision();
        $revision2 = new Revision();
        $review    = new CodeReview();

        $this->repository->expects($this->once())->method('find')->with(456)->willReturn($review);
        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([$revision1, $revision2]);
        $this->gitShowService->expects($this->once())
            ->method('getFileContents')
            ->with($revision2, 'src/test.ts')
            ->willReturn('typescript content');

        $result = ($this->tool)(456, 'src/test.ts');
        static::assertSame('typescript content', $result);
    }
}

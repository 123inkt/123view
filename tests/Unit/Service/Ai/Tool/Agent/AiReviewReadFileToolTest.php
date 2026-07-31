<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai\Tool\Agent;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Ai\Tool\Agent\AiReviewReadFileTool;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\Git\Show\LockableGitShowService;
use DR\Review\Tests\AbstractTestCase;
use Mcp\Exception\ToolCallException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(AiReviewReadFileTool::class)]
class AiReviewReadFileToolTest extends AbstractTestCase
{
    private CodeReviewRepository&MockObject      $repository;
    private CodeReviewRevisionService&MockObject $revisionService;
    private LockableGitShowService&MockObject    $gitShowService;
    private AiReviewReadFileTool                 $tool;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository      = $this->createMock(CodeReviewRepository::class);
        $this->revisionService = $this->createMock(CodeReviewRevisionService::class);
        $this->gitShowService  = $this->createMock(LockableGitShowService::class);
        $this->tool            = new AiReviewReadFileTool($this->logger, $this->repository, $this->revisionService, $this->gitShowService);
    }

    public function testInvokeShouldThrowExceptionWhenReviewNotFound(): void
    {
        $this->repository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->revisionService->expects($this->never())->method('getRevisions');
        $this->gitShowService->expects($this->never())->method('getFileContents');

        $this->expectException(ToolCallException::class);
        ($this->tool)(123, 'path/to/file.php');
    }

    public function testInvokeShouldReturnFullSmallFile(): void
    {
        $revision = new Revision();
        $review   = new CodeReview();

        $this->repository->expects($this->once())->method('find')->willReturn($review);
        $this->revisionService->expects($this->once())->method('getRevisions')->willReturn([$revision]);
        $this->gitShowService->expects($this->once())->method('getFileContents')->willReturn("line1\nline2\nline3");

        $result = ($this->tool)(123, 'path/to/file.php');

        static::assertSame("line1\nline2\nline3", $result['content']);
        static::assertSame(0, $result['startLine']);
        static::assertSame(3, $result['endLine']);
        static::assertSame(3, $result['totalLines']);
        static::assertFalse($result['truncated']);
    }

    public function testInvokeShouldPaginateByLineOffsetAndLimit(): void
    {
        $revision = new Revision();
        $review   = new CodeReview();
        $lines    = implode("\n", array_map(static fn(int $i) => 'line' . $i, range(0, 999)));

        $this->repository->expects($this->once())->method('find')->willReturn($review);
        $this->revisionService->expects($this->once())->method('getRevisions')->willReturn([$revision]);
        $this->gitShowService->expects($this->once())->method('getFileContents')->willReturn($lines);

        $result = ($this->tool)(123, 'path/to/file.php', 10, 5);

        static::assertSame("line10\nline11\nline12\nline13\nline14", $result['content']);
        static::assertSame(10, $result['startLine']);
        static::assertSame(15, $result['endLine']);
        static::assertSame(1000, $result['totalLines']);
        static::assertTrue($result['truncated']);
    }

    public function testInvokeShouldNeverExceedMaxLinesEvenWhenLimitRequestsMore(): void
    {
        $revision = new Revision();
        $review   = new CodeReview();
        $lines    = implode("\n", array_fill(0, 1000, 'x'));

        $this->repository->expects($this->once())->method('find')->willReturn($review);
        $this->revisionService->expects($this->once())->method('getRevisions')->willReturn([$revision]);
        $this->gitShowService->expects($this->once())->method('getFileContents')->willReturn($lines);

        $result = ($this->tool)(123, 'path/to/file.php', 0, 10_000);

        static::assertSame(AiReviewReadFileTool::MAX_LINES, $result['endLine']);
    }

    public function testInvokeShouldCapReturnedCharacters(): void
    {
        $revision = new Revision();
        $review   = new CodeReview();

        $this->repository->expects($this->once())->method('find')->willReturn($review);
        $this->revisionService->expects($this->once())->method('getRevisions')->willReturn([$revision]);
        $this->gitShowService->expects($this->once())->method('getFileContents')->willReturn(str_repeat('x', AiReviewReadFileTool::MAX_CHARACTERS + 5000));

        $result = ($this->tool)(123, 'path/to/file.php');

        static::assertSame(AiReviewReadFileTool::MAX_CHARACTERS, strlen($result['content']));
        static::assertTrue($result['truncated']);
    }
}

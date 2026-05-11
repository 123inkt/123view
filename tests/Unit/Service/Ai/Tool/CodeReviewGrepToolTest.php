<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai\Tool;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Exception\Ai\CodeReviewNotFoundException;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Ai\Tool\CodeReviewGrepTool;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\Git\Grep\LockableGitGrepService;
use DR\Review\Tests\AbstractTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CodeReviewGrepTool::class)]
class CodeReviewGrepToolTest extends AbstractTestCase
{
    private CodeReviewRepository&MockObject      $repository;
    private CodeReviewRevisionService&MockObject $revisionService;
    private LockableGitGrepService&MockObject    $grepService;
    private CodeReviewGrepTool                   $tool;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository      = $this->createMock(CodeReviewRepository::class);
        $this->revisionService = $this->createMock(CodeReviewRevisionService::class);
        $this->grepService     = $this->createMock(LockableGitGrepService::class);
        $this->tool            = new CodeReviewGrepTool($this->logger, $this->repository, $this->revisionService, $this->grepService);
    }

    public function testInvokeShouldThrowExceptionForInvalidRegex(): void
    {
        $this->repository->expects($this->never())->method('find');
        $this->revisionService->expects($this->never())->method('getRevisions');
        $this->grepService->expects($this->never())->method('grep');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The provided pattern is not a valid regex pattern: [invalid');
        ($this->tool)(123, '[invalid');
    }

    public function testInvokeShouldThrowExceptionWhenReviewNotFound(): void
    {
        $this->repository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->revisionService->expects($this->never())->method('getRevisions');
        $this->grepService->expects($this->never())->method('grep');

        $this->expectException(CodeReviewNotFoundException::class);
        ($this->tool)(123, 'validPattern');
    }

    public function testInvokeShouldThrowExceptionWhenNoRevisions(): void
    {
        $review = new CodeReview();
        $this->repository->expects($this->once())->method('find')->with(123)->willReturn($review);
        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([]);
        $this->grepService->expects($this->never())->method('grep');

        $this->expectException(CodeReviewNotFoundException::class);
        ($this->tool)(123, 'validPattern');
    }

    public function testInvokeShouldReturnNoResultsFoundWhenGrepReturnsNull(): void
    {
        $revision = new Revision();
        $review   = new CodeReview();

        $this->repository->expects($this->once())->method('find')->with(123)->willReturn($review);
        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->grepService->expects($this->once())
            ->method('grep')
            ->with($revision, 'searchPattern', null)
            ->willReturn(null);

        $result = ($this->tool)(123, 'searchPattern');
        static::assertSame('No results found', $result);
    }

    public function testInvokeShouldReturnGrepResults(): void
    {
        $revision = new Revision();
        $review   = new CodeReview();

        $this->repository->expects($this->once())->method('find')->with(456)->willReturn($review);
        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->grepService->expects($this->once())
            ->method('grep')
            ->with($revision, 'function', null)
            ->willReturn("src/file.php:10:function test()");

        $result = ($this->tool)(456, 'function');
        static::assertSame("src/file.php:10:function test()", $result);
    }

    public function testInvokeShouldPassContextToGrepService(): void
    {
        $revision = new Revision();
        $review   = new CodeReview();

        $this->repository->expects($this->once())->method('find')->with(789)->willReturn($review);
        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->grepService->expects($this->once())
            ->method('grep')
            ->with($revision, 'class', 3)
            ->willReturn("src/file.php:8-\nsrc/file.php:9-\nsrc/file.php:10:class Test");

        $result = ($this->tool)(789, 'class', 3);
        static::assertSame("src/file.php:8-\nsrc/file.php:9-\nsrc/file.php:10:class Test", $result);
    }

    public function testInvokeShouldUseLastRevision(): void
    {
        $revision1 = new Revision();
        $revision2 = new Revision();
        $review    = new CodeReview();

        $this->repository->expects($this->once())->method('find')->with(100)->willReturn($review);
        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([$revision1, $revision2]);
        $this->grepService->expects($this->once())
            ->method('grep')
            ->with($revision2, 'pattern', null)
            ->willReturn('result');

        $result = ($this->tool)(100, 'pattern');
        static::assertSame('result', $result);
    }

    public function testInvokeShouldPassNullContextWhenContextIsZero(): void
    {
        $revision = new Revision();
        $review   = new CodeReview();

        $this->repository->expects($this->once())->method('find')->with(200)->willReturn($review);
        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->grepService->expects($this->once())
            ->method('grep')
            ->with($revision, 'test', null)
            ->willReturn('match');

        $result = ($this->tool)(200, 'test', 0);
        static::assertSame('match', $result);
    }
}

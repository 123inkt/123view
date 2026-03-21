<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai\Tool;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Exception\Ai\CodeReviewFileNotFoundException;
use DR\Review\Exception\Ai\CodeReviewNotFoundException;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Ai\Tool\CodeReviewFileTool;
use DR\Review\Service\Git\Show\LockableGitShowService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CodeReviewFileTool::class)]
class CodeReviewFileToolTest extends AbstractTestCase
{
    private CodeReviewRepository&MockObject    $repository;
    private LockableGitShowService&MockObject  $gitShowService;
    private CodeReviewFileTool                 $tool;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository     = $this->createMock(CodeReviewRepository::class);
        $this->gitShowService = $this->createMock(LockableGitShowService::class);
        $this->tool           = new CodeReviewFileTool($this->logger, $this->repository, $this->gitShowService);
    }

    public function testInvokeShouldThrowExceptionWhenReviewNotFound(): void
    {
        $this->repository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->gitShowService->expects($this->never())->method('getFileContents');

        $this->expectException(CodeReviewNotFoundException::class);
        ($this->tool)(123, 'path/to/file.php');
    }

    public function testInvokeShouldThrowExceptionWhenNoRevisions(): void
    {
        $review = new CodeReview();
        $this->repository->expects($this->once())->method('find')->with(123)->willReturn($review);
        $this->gitShowService->expects($this->never())->method('getFileContents');

        $this->expectException(CodeReviewFileNotFoundException::class);
        ($this->tool)(123, 'path/to/file.php');
    }

    public function testInvokeShouldReturnFileContents(): void
    {
        $repositoryEntity = new Repository();
        $revision         = new Revision();
        $revision->setRepository($repositoryEntity);

        $review = new CodeReview();
        $review->getRevisions()->add($revision);

        $this->repository->expects($this->once())->method('find')->with(123)->willReturn($review);
        $this->gitShowService->expects($this->once())
            ->method('getFileContents')
            ->with($revision, 'path/to/file.php')
            ->willReturn('file contents');

        $result = ($this->tool)(123, 'path/to/file.php');
        static::assertSame('file contents', $result);
    }

    public function testInvokeShouldUseLastRevision(): void
    {
        $repositoryEntity = new Repository();

        $revision1 = new Revision();
        $revision1->setRepository($repositoryEntity);

        $revision2 = new Revision();
        $revision2->setRepository($repositoryEntity);

        $review = new CodeReview();
        $review->getRevisions()->add($revision1);
        $review->getRevisions()->add($revision2);

        $this->repository->expects($this->once())->method('find')->with(456)->willReturn($review);
        $this->gitShowService->expects($this->once())
            ->method('getFileContents')
            ->with($revision2, 'src/test.ts')
            ->willReturn('typescript content');

        $result = ($this->tool)(456, 'src/test.ts');
        static::assertSame('typescript content', $result);
    }
}

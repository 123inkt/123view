<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai\Tool;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Exception\Ai\CodeReviewFileNotFoundException;
use DR\Review\Exception\Ai\CodeReviewNotFoundException;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Ai\Tool\CodeReviewListFilesTool;
use DR\Review\Service\Git\LsTree\LockableLsTreeService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CodeReviewListFilesTool::class)]
class CodeReviewListFilesToolTest extends AbstractTestCase
{
    private CodeReviewRepository&MockObject  $repository;
    private LockableLsTreeService&MockObject $lsTreeService;
    private CodeReviewListFilesTool          $tool;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository    = $this->createMock(CodeReviewRepository::class);
        $this->lsTreeService = $this->createMock(LockableLsTreeService::class);
        $this->tool          = new CodeReviewListFilesTool($this->logger, $this->repository, $this->lsTreeService);
    }

    public function testInvokeShouldThrowExceptionWhenReviewNotFound(): void
    {
        $this->repository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->lsTreeService->expects($this->never())->method('listFiles');

        $this->expectException(CodeReviewNotFoundException::class);
        ($this->tool)(123, 'src/');
    }

    public function testInvokeShouldThrowExceptionWhenNoRevisions(): void
    {
        $review = new CodeReview();
        $this->repository->expects($this->once())->method('find')->with(123)->willReturn($review);
        $this->lsTreeService->expects($this->never())->method('listFiles');

        $this->expectException(CodeReviewFileNotFoundException::class);
        ($this->tool)(123, 'src/');
    }

    public function testInvokeShouldReturnFileList(): void
    {
        $repositoryEntity = new Repository();
        $revision         = new Revision();
        $revision->setRepository($repositoryEntity);

        $review = new CodeReview();
        $review->getRevisions()->add($revision);

        $fileList = ['src/file1.php', 'src/file2.php', 'src/Service/file3.php'];

        $this->repository->expects($this->once())->method('find')->with(123)->willReturn($review);
        $this->lsTreeService->expects($this->once())
            ->method('listFiles')
            ->with($revision, 'src/')
            ->willReturn($fileList);

        $result = ($this->tool)(123, 'src/');
        static::assertSame($fileList, $result);
    }

    public function testInvokeShouldSupportGlobPatterns(): void
    {
        $repositoryEntity = new Repository();
        $revision         = new Revision();
        $revision->setRepository($repositoryEntity);

        $review = new CodeReview();
        $review->getRevisions()->add($revision);

        $fileList = ['src/Service/Test.php', 'src/Service/SubDir/Another.php'];

        $this->repository->expects($this->once())->method('find')->with(456)->willReturn($review);
        $this->lsTreeService->expects($this->once())
            ->method('listFiles')
            ->with($revision, 'src/Service/**/*.php')
            ->willReturn($fileList);

        $result = ($this->tool)(456, 'src/Service/**/*.php');
        static::assertSame($fileList, $result);
    }
}

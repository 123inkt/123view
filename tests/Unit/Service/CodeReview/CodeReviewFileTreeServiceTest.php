<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Model\Review\DirectoryTreeNode;
use DR\Review\Service\CodeHighlight\HighlightedFileService;
use DR\Review\Service\CodeReview\CodeReviewFileTreeService;
use DR\Review\Service\CodeReview\FileTreeGenerator;
use DR\Review\Service\Git\Diff\DiffFileUpdater;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(CodeReviewFileTreeService::class)]
class CodeReviewFileTreeServiceTest extends AbstractTestCase
{
    private ReviewDiffServiceInterface&MockObject $diffService;
    private FileTreeGenerator&MockObject          $treeGenerator;
    private DiffFileUpdater&MockObject            $diffFileUpdater;
    private CodeReviewFileTreeService             $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->diffService     = $this->createMock(ReviewDiffServiceInterface::class);
        $this->treeGenerator   = $this->createMock(FileTreeGenerator::class);
        $this->diffFileUpdater = $this->createMock(DiffFileUpdater::class);
        $this->service         = new CodeReviewFileTreeService($this->diffService, $this->treeGenerator, $this->diffFileUpdater);
    }

    /**
     * @throws Throwable
     */
    public function testGetFileTreeWithoutRevisions(): void
    {
        $repository = new Repository();
        $review     = new CodeReview();
        $review->setRepository($repository);
        $options = new FileDiffOptions(10, DiffComparePolicy::ALL);

        /** @var DirectoryTreeNode<DiffFile>&MockObject $treeNode */
        $treeNode = $this->createMock(DirectoryTreeNode::class);

        $this->diffService->expects(self::never())->method('getDiffForRevisions');
        $this->diffService->expects(self::never())->method('getDiffForBranch');
        $this->diffFileUpdater->expects(self::once())->method('update')->with([], 6, HighlightedFileService::MAX_LINE_COUNT);
        $this->treeGenerator->expects(self::once())->method('generate')->with([])->willReturn($treeNode);
        $treeNode->expects(self::once())->method('flatten')->willReturnSelf();
        $treeNode->expects(self::once())->method('sort')->willReturnSelf();

        static::assertSame([$treeNode, []], $this->service->getFileTree($review, [], $options));
    }

    /**
     * @throws Throwable
     */
    public function testGetFileTreeForBranchReview(): void
    {
        $repository = new Repository();
        $review     = new CodeReview();
        $review->setType(CodeReviewType::BRANCH);
        $review->setReferenceId('branch');
        $review->setRepository($repository);
        $options   = new FileDiffOptions(10, DiffComparePolicy::ALL);
        $revisions = [new Revision()];
        $files     = [new DiffFile()];

        /** @var DirectoryTreeNode<DiffFile>&MockObject $treeNode */
        $treeNode = $this->createMock(DirectoryTreeNode::class);

        $this->diffService->expects(self::once())->method('getDiffForBranch')->with($repository, $revisions, 'branch', $options)->willReturn($files);
        $this->diffFileUpdater->expects(self::once())->method('update')->with($files, 6, HighlightedFileService::MAX_LINE_COUNT)->willReturn($files);
        $this->treeGenerator->expects(self::once())->method('generate')->with($files)->willReturn($treeNode);
        $treeNode->expects(self::once())->method('flatten')->willReturnSelf();
        $treeNode->expects(self::once())->method('sort')->willReturnSelf();

        static::assertSame([$treeNode, $files], $this->service->getFileTree($review, $revisions, $options));
    }

    /**
     * @throws Throwable
     */
    public function testGetFileTreeForRevisionsReview(): void
    {
        $repository = new Repository();
        $review     = new CodeReview();
        $review->setType(CodeReviewType::COMMITS);
        $review->setRepository($repository);
        $options   = new FileDiffOptions(10, DiffComparePolicy::ALL);
        $revisions = [new Revision()];
        $files     = [new DiffFile()];

        /** @var DirectoryTreeNode<DiffFile>&MockObject $treeNode */
        $treeNode = $this->createMock(DirectoryTreeNode::class);

        $this->diffService->expects(self::once())->method('getDiffForRevisions')->with($repository, $revisions, $options)->willReturn($files);
        $this->diffFileUpdater->expects(self::once())->method('update')->with($files, 6, HighlightedFileService::MAX_LINE_COUNT)->willReturn($files);
        $this->treeGenerator->expects(self::once())->method('generate')->with($files)->willReturn($treeNode);
        $treeNode->expects(self::once())->method('flatten')->willReturnSelf();
        $treeNode->expects(self::once())->method('sort')->willReturnSelf();

        static::assertSame([$treeNode, $files], $this->service->getFileTree($review, $revisions, $options));
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Model\Review\DirectoryTreeNode;
use DR\Review\Service\CodeReview\CodeReviewFileService;
use DR\Review\Service\CodeReview\DiffFinder;
use DR\Review\Service\CodeReview\FileTreeGenerator;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub\ReturnCallback;
use Symfony\Contracts\Cache\CacheInterface;
use Throwable;

/**
 * @coversDefaultClass \DR\Review\Service\CodeReview\CodeReviewFileService
 * @covers ::__construct
 */
class CodeReviewFileServiceTest extends AbstractTestCase
{
    private CacheInterface&MockObject             $cache;
    private ReviewDiffServiceInterface&MockObject $diffService;
    private FileTreeGenerator&MockObject          $treeGenerator;
    private DiffFinder&MockObject                 $diffFinder;
    private CodeReviewFileService                 $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->cache = $this->createMock(CacheInterface::class);

        $this->diffService   = $this->createMock(ReviewDiffServiceInterface::class);
        $this->treeGenerator = $this->createMock(FileTreeGenerator::class);
        $this->diffFinder    = $this->createMock(DiffFinder::class);
        $this->service       = new CodeReviewFileService($this->cache, $this->diffService, $this->treeGenerator, $this->diffFinder);
    }

    /**
     * @covers ::getFiles
     * @covers ::getReviewCacheKey
     * @covers ::getDiffFileCacheKey
     * @throws Throwable
     */
    public function testGetFiles(): void
    {
        $repository = new Repository();
        $repository->setId(789);
        $review = new CodeReview();
        $review->setId(123);
        $review->setRepository($repository);
        $revision = new Revision();
        $revision->setId(456);
        $revision->setCommitHash('hash');

        $diffFileA = new DiffFile();
        $diffFileB = new DiffFile();
        /** @var DirectoryTreeNode<DiffFile> $tree */
        $tree = new DirectoryTreeNode('node');

        $this->cache->expects(self::exactly(3))
            ->method('get')
            ->willReturnOnConsecutiveCalls(
                new ReturnCallback(static fn($repository, $callback) => $callback()),
                new ReturnCallback(static fn($repository, $callback) => $callback()),
                new ReturnCallback(static fn($repository, $callback) => $diffFileA)
            );
        $this->diffService->expects(self::once())->method('getDiffFiles')
            ->with($repository, [$revision], new FileDiffOptions(9999999))
            ->willReturn([$diffFileA], [$diffFileB]);

        $this->treeGenerator->expects(self::once())->method('generate')->with([$diffFileA])->willReturn($tree);
        $this->diffFinder->expects(self::once())->method('findFileByPath')->with([$diffFileA], 'filepath')->willReturn($diffFileB);

        $result = $this->service->getFiles($review, [$revision], 'filepath');
        static::assertSame([$tree, $diffFileA], $result);
    }
}

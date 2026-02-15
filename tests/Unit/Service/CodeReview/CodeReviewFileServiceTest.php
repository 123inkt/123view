<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview;

use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Model\Review\DirectoryTreeNode;
use DR\Review\Service\CodeReview\CodeReviewFileService;
use DR\Review\Service\CodeReview\CodeReviewFileTreeService;
use DR\Review\Service\CodeReview\DiffFinder;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\Tests\CacheTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Cache\Adapter\AbstractAdapter as AbstractCacheAdapter;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Throwable;

#[CoversClass(CodeReviewFileService::class)]
class CodeReviewFileServiceTest extends AbstractTestCase
{
    use CacheTestTrait;

    private CacheInterface&AdapterInterface&MockObject $revisionCache;
    private DiffFinder&MockObject                      $diffFinder;
    private CodeReviewFileTreeService&MockObject       $fileTreeService;
    private CodeReviewFileService                      $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->revisionCache   = $this->createMock(AbstractCacheAdapter::class);
        $this->diffFinder      = $this->createMock(DiffFinder::class);
        $this->fileTreeService = $this->createMock(CodeReviewFileTreeService::class);
        $this->service         = new CodeReviewFileService(
            $this->revisionCache,
            $this->diffFinder,
            $this->fileTreeService
        );
    }

    /**
     * @throws Throwable
     */
    public function testGetFilesWithCache(): void
    {
        $review = new CodeReview();
        $review->setId(123);
        $revision = new Revision();
        $revision->setId(456);
        $revision->setCommitHash('hash');
        $options = new FileDiffOptions(5, DiffComparePolicy::IGNORE);

        $diffFileA = new DiffFile();
        $diffFileB = new DiffFile();
        /** @var DirectoryTreeNode<DiffFile> $tree */
        $tree = new DirectoryTreeNode('node');
        $tree->addNode(['file'], $diffFileA);

        $cacheItem = self::createCacheItem('key', $tree, true);

        $this->revisionCache->expects($this->once())->method('getItem')->willReturn($cacheItem);
        $this->revisionCache->expects($this->once())->method('get')->willReturn($diffFileA);
        $this->diffFinder->expects($this->once())->method('findFileByPath')->with([$diffFileA], 'filepath')->willReturn($diffFileB);
        $this->fileTreeService->expects($this->never())->method('getFileTree');

        $result = $this->service->getFiles($review, [$revision], 'filepath', $options);
        static::assertSame([$tree, $diffFileA], $result);
    }

    /**
     * @throws Throwable
     */
    public function testGetFilesWithoutCache(): void
    {
        $review = new CodeReview();
        $review->setId(123);
        $revision = new Revision();
        $revision->setId(456);
        $revision->setCommitHash('hash');
        $options = new FileDiffOptions(5, DiffComparePolicy::IGNORE);

        $diffFileA = new DiffFile();
        $diffFileB = new DiffFile();
        /** @var DirectoryTreeNode<DiffFile> $tree */
        $tree = new DirectoryTreeNode('node');
        $tree->addNode(['file'], $diffFileA);

        $cacheItem = self::createCacheItem('key', null, false);

        $this->revisionCache->expects($this->once())->method('getItem')->willReturn($cacheItem);
        $this->fileTreeService->expects($this->once())->method('getFileTree')->with($review, [$revision])->willReturn([$tree, [$diffFileA]]);
        $this->revisionCache->expects($this->exactly(3))->method('get')->willReturn(null, null, $diffFileA);
        $this->diffFinder->expects($this->once())->method('findFileByPath')->with([$diffFileA], 'filepath')->willReturn($diffFileB);

        $result = $this->service->getFiles($review, [$revision], 'filepath', $options);
        static::assertSame([$tree, $diffFileA], $result);
    }
}

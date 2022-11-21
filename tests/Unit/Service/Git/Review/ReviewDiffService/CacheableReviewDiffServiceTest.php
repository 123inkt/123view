<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\Review\ReviewDiffService;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Service\Git\Review\FileDiffOptions;
use DR\GitCommitNotification\Service\Git\Review\ReviewDiffService\CacheableReviewDiffService;
use DR\GitCommitNotification\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\Cache\CacheInterface;
use Throwable;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\Review\ReviewDiffService\CacheableReviewDiffService
 * @covers ::__construct
 */
class CacheableReviewDiffServiceTest extends AbstractTestCase
{
    private CacheInterface&MockObject             $cache;
    private ReviewDiffServiceInterface&MockObject $diffService;
    private CacheableReviewDiffService            $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->cache       = $this->createMock(CacheInterface::class);
        $this->diffService = $this->createMock(ReviewDiffServiceInterface::class);
        $this->service     = new CacheableReviewDiffService($this->cache, $this->diffService);
    }

    /**
     * @covers ::getDiffFiles
     * @throws Throwable
     */
    public function testGetDiffFilesWithoutRevisions(): void
    {
        $this->cache->expects(self::never())->method('get');

        static::assertSame([], $this->service->getDiffFiles(new Repository(), []));
    }

    /**
     * @covers ::getDiffFiles
     * @throws Throwable
     */
    public function testGetDiffFilesWithRevisions(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $revision = new Revision();
        $revision->setCommitHash('hash');
        $diffFile = new DiffFile();
        $options  = new FileDiffOptions(20);

        $this->cache->expects(self::once())
            ->method('get')
            ->with('diff-files-123-hash-udl-20')
            ->willReturnCallback(static fn($repository, $callback) => $callback());
        $this->diffService->expects(self::once())->method('getDiffFiles')->with($repository, [$revision])->willReturn([$diffFile]);

        static::assertSame([$diffFile], $this->service->getDiffFiles($repository, [$revision], $options));
    }
}

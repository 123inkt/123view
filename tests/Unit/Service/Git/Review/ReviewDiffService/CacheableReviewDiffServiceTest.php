<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Review\ReviewDiffService;

use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Review\ReviewDiffService\CacheableReviewDiffService;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\Cache\CacheInterface;
use Throwable;

/**
 * @coversDefaultClass \DR\Review\Service\Git\Review\ReviewDiffService\CacheableReviewDiffService
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
     * @covers ::getDiffForRevisions
     * @throws Throwable
     */
    public function testGetDiffFilesWithoutRevisions(): void
    {
        $this->cache->expects(self::never())->method('get');

        static::assertSame([], $this->service->getDiffForRevisions(new Repository(), []));
    }

    /**
     * @covers ::getDiffForRevisions
     * @throws Throwable
     */
    public function testGetDiffFilesWithRevisions(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $revision = new Revision();
        $revision->setCommitHash('hash');
        $diffFile = new DiffFile();
        $options  = new FileDiffOptions(20, DiffComparePolicy::TRIM);

        $this->cache->expects(self::once())
            ->method('get')
            ->with('diff-files-123-hash-fdo-20-trim')
            ->willReturnCallback(static fn($repository, $callback) => $callback());
        $this->diffService->expects(self::once())->method('getDiffForRevisions')->with($repository, [$revision])->willReturn([$diffFile]);

        static::assertSame([$diffFile], $this->service->getDiffForRevisions($repository, [$revision], $options));
    }
}

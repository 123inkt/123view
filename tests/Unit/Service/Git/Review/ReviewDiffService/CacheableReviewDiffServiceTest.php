<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Review\ReviewDiffService;

use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Review\ReviewDiffService\CacheableReviewDiffService;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\Cache\CacheInterface;
use Throwable;

#[CoversClass(CacheableReviewDiffService::class)]
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
     * @throws Throwable
     */
    public function testGetDiffFilesWithoutRevisions(): void
    {
        $this->cache->expects(self::never())->method('get');

        static::assertSame([], $this->service->getDiffForRevisions(new Repository(), []));
    }

    /**
     * @throws Throwable
     */
    public function testGetDiffFilesWithRevisions(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $revision = new Revision();
        $revision->setCommitHash('hash');
        $diffFile = new DiffFile();
        $options  = new FileDiffOptions(20, DiffComparePolicy::TRIM, visibleLines: 123);

        $this->cache->expects($this->once())
            ->method('get')
            ->with('diff-files-revision-123-hash-fdo-20-trim-commits-123')
            ->willReturnCallback(static fn($repository, $callback) => $callback());
        $this->diffService->expects($this->once())->method('getDiffForRevisions')->with($repository, [$revision], $options)->willReturn([$diffFile]);

        static::assertSame([$diffFile], $this->service->getDiffForRevisions($repository, [$revision], $options));
    }

    /**
     * @throws Throwable
     */
    public function testGetDiffForBranch(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $review   = (new CodeReview())->setRepository($repository);
        $revision = new Revision();
        $revision->setCommitHash('hash');
        $diffFile = new DiffFile();
        $options  = new FileDiffOptions(20, DiffComparePolicy::TRIM, visibleLines: 123);

        $this->cache->expects($this->once())
            ->method('get')
            ->with('9450af3cf2575eabc1e18a546e18dc481483b352edc02f14d7aa0e3fdd513d10')
            ->willReturnCallback(static fn($repository, $callback) => $callback());
        $this->diffService->expects($this->once())->method('getDiffForBranch')
            ->with($review, [$revision], 'branch', $options)
            ->willReturn([$diffFile]);

        static::assertSame([$diffFile], $this->service->getDiffForBranch($review, [$revision], 'branch', $options));
    }
}

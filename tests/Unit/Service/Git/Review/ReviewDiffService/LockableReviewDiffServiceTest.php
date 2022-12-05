<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Review\ReviewDiffService;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\Revision;
use DR\Review\Service\Git\GitRepositoryLockManager;
use DR\Review\Service\Git\Review\ReviewDiffService\LockableReviewDiffService;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

/**
 * @coversDefaultClass \DR\Review\Service\Git\Review\ReviewDiffService\LockableReviewDiffService
 * @covers ::__construct
 */
class LockableReviewDiffServiceTest extends AbstractTestCase
{
    private GitRepositoryLockManager&MockObject   $lockManager;
    private ReviewDiffServiceInterface&MockObject $diffService;
    private LockableReviewDiffService             $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->lockManager = $this->createMock(GitRepositoryLockManager::class);
        $this->diffService = $this->createMock(ReviewDiffServiceInterface::class);
        $this->service     = new LockableReviewDiffService($this->lockManager, $this->diffService);
    }

    /**
     * @covers ::getDiffFiles
     * @throws Throwable
     */
    public function testGetDiffFiles(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $revision = new Revision();
        $revision->setCommitHash('hash');
        $diffFile = new DiffFile();

        $this->lockManager->expects(self::once())
            ->method('start')
            ->with($repository)
            ->willReturnCallback(static fn($repository, $callback) => $callback());
        $this->diffService->expects(self::once())->method('getDiffFiles')->with($repository, [$revision])->willReturn([$diffFile]);

        static::assertSame([$diffFile], $this->service->getDiffFiles($repository, [$revision]));
    }
}

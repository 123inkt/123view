<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Review\ReviewDiffService;

use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Service\Git\GitRepositoryLockManager;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Review\ReviewDiffService\LockableReviewDiffService;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(LockableReviewDiffService::class)]
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
     * @throws Throwable
     */
    public function testGetDiffForRevisions(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $revision = new Revision();
        $revision->setCommitHash('hash');
        $options  = new FileDiffOptions(10, DiffComparePolicy::TRIM);
        $diffFile = new DiffFile();

        $this->lockManager->expects($this->once())
            ->method('start')
            ->with($repository)
            ->willReturnCallback(static fn($repository, $callback) => $callback());
        $this->diffService->expects($this->once())->method('getDiffForRevisions')->with($repository, [$revision], $options)->willReturn([$diffFile]);

        static::assertSame([$diffFile], $this->service->getDiffForRevisions($repository, [$revision], $options));
    }

    /**
     * @throws Throwable
     */
    public function testGetDiffForBranch(): void
    {
        $repository = (new Repository())->setId(123);
        $review     = (new CodeReview())->setRepository($repository);
        $revision   = new Revision();
        $revision->setCommitHash('hash');
        $options  = new FileDiffOptions(10, DiffComparePolicy::TRIM);
        $diffFile = new DiffFile();

        $this->lockManager->expects($this->once())
            ->method('start')
            ->with($repository)
            ->willReturnCallback(static fn($review, $callback) => $callback());
        $this->diffService->expects($this->once())
            ->method('getDiffForBranch')
            ->with($review, [$revision], 'branch', $options)
            ->willReturn([$diffFile]);

        static::assertSame([$diffFile], $this->service->getDiffForBranch($review, [$revision], 'branch', $options));
    }
}

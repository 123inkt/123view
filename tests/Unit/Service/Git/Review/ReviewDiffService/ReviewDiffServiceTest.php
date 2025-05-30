<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Review\ReviewDiffService;

use Doctrine\Common\Collections\ArrayCollection;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Service\Git\Diff\GitDiffService;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffService;
use DR\Review\Service\Git\Review\Strategy\ReviewDiffStrategyInterface;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Throwable;

#[CoversClass(ReviewDiffService::class)]
class ReviewDiffServiceTest extends AbstractTestCase
{
    private GitDiffService&MockObject              $diffService;
    private ReviewDiffStrategyInterface&MockObject $strategyA;
    private ReviewDiffStrategyInterface&MockObject $strategyB;
    private ReviewDiffService                      $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->diffService = $this->createMock(GitDiffService::class);
        $this->strategyA   = $this->createMock(ReviewDiffStrategyInterface::class);
        $this->strategyB   = $this->createMock(ReviewDiffStrategyInterface::class);
        $this->service     = new ReviewDiffService($this->diffService, new ArrayCollection([$this->strategyA, $this->strategyB]));
    }

    /**
     * @throws Throwable
     */
    public function testGetDiffFilesEmptyRevisions(): void
    {
        $repository = new Repository();
        $repository->setId(123);

        $this->diffService->expects(self::never())->method('getDiffFromRevision');
        static::assertSame([], $this->service->getDiffForRevisions($repository, []));
    }

    /**
     * @throws Throwable
     */
    public function testGetDiffFilesSingleRevision(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $revision = new Revision();
        $revision->setCommitHash('hash');
        $diffFile = new DiffFile();

        $this->diffService->expects($this->once())->method('getDiffFromRevision')->with($revision)->willReturn([$diffFile]);
        static::assertSame([$diffFile], $this->service->getDiffForRevisions($repository, [$revision]));
    }

    /**
     * @throws Throwable
     */
    public function testGetDiffFilesMultipleRevisionsFirstStrategy(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $revisionA = new Revision();
        $revisionB = new Revision();
        $diffFile  = new DiffFile();

        $this->diffService->expects(self::never())->method('getDiffFromRevision');
        $this->strategyA->expects($this->once())->method('getDiffFiles')->with($repository, [$revisionA, $revisionB])->willReturn([$diffFile]);

        static::assertSame([$diffFile], $this->service->getDiffForRevisions($repository, [$revisionA, $revisionB]));
    }

    /**
     * @throws Throwable
     */
    public function testGetDiffFilesMultipleRevisionsSecondStrategy(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $revisionA = new Revision();
        $revisionB = new Revision();
        $diffFile  = new DiffFile();

        $this->diffService->expects(self::never())->method('getDiffFromRevision');
        $this->strategyA->expects($this->once())->method('getDiffFiles')->willThrowException(new RuntimeException());
        $this->strategyB->expects($this->once())->method('getDiffFiles')->with($repository, [$revisionA, $revisionB])->willReturn([$diffFile]);

        static::assertSame([$diffFile], $this->service->getDiffForRevisions($repository, [$revisionA, $revisionB]));
    }

    /**
     * @throws Throwable
     */
    public function testGetDiffFilesMultipleRevisionsNoStrategy(): void
    {
        $repository = new Repository();
        $revisionA  = new Revision();
        $revisionB  = new Revision();

        $this->diffService->expects(self::never())->method('getDiffFromRevision');
        $this->strategyA->expects($this->once())->method('getDiffFiles')->willThrowException(new RuntimeException());
        $this->strategyB->expects($this->once())->method('getDiffFiles')->willThrowException(new RuntimeException());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to fetch diff for revisions. All strategies exhausted');
        $this->service->getDiffForRevisions($repository, [$revisionA, $revisionB]);
    }

    /**
     * @throws Throwable
     */
    public function testGetDiffForBranch(): void
    {
        $repository = new Repository();
        $review   = (new CodeReview())->setRepository($repository)->setTargetBranch('master');
        $revision = new Revision();
        $options  = new FileDiffOptions(10, DiffComparePolicy::TRIM);
        $diffFile = new DiffFile();

        $this->diffService->expects($this->once())
            ->method('getBundledDiffFromBranch')
            ->with($repository, 'branch', 'origin/master', $options)
            ->willReturn([$diffFile]);

        static::assertSame([$diffFile], $this->service->getDiffForBranch($review, [$revision], 'branch', $options));
    }
}

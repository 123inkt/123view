<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Review\ReviewDiffService;

use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Review\ReviewDiffService\RecoverableReviewDiffService;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Throwable;

#[CoversClass(RecoverableReviewDiffService::class)]
class RecoverableReviewDiffServiceTest extends AbstractTestCase
{
    private ReviewDiffServiceInterface&MockObject $diffService;
    private RecoverableReviewDiffService          $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->diffService = $this->createMock(ReviewDiffServiceInterface::class);
        $this->service     = new RecoverableReviewDiffService($this->diffService);
    }

    /**
     * @throws Throwable
     */
    public function testGetDiffForRevisions(): void
    {
        $repository = new Repository();
        $revisions  = [new Revision()];
        $options    = new FileDiffOptions(5, DiffComparePolicy::ALL);

        $this->diffService->expects($this->once())->method('getDiffForRevisions')->with($repository, $revisions, $options);

        $this->service->getDiffForRevisions($repository, $revisions, $options);
    }

    /**
     * @throws Throwable
     */
    public function testGetDiffForBranchExpectTwoAttempts(): void
    {
        $callCount        = new stdClass();
        $callCount->count = 0;
        $repository       = (new Repository())->setMainBranchName('master');
        $review           = (new CodeReview())->setTargetBranch('foobar')->setRepository($repository);
        $revisions        = [new Revision()];
        $options          = new FileDiffOptions(5, DiffComparePolicy::ALL);
        $branchName       = 'branch';

        $exception = $this->createMock(ProcessFailedException::class);

        $this->diffService->expects($this->exactly(2))->method('getDiffForBranch')
            ->with($review, $revisions, $branchName, $options)
            ->willThrowException($exception);

        $this->expectException(ProcessFailedException::class);
        $this->service->getDiffForBranch($review, $revisions, $branchName, $options);
    }

    /**
     * @throws Throwable
     */
    public function testGetDiffForBranchExpectOneAttemptOnMasterBranch(): void
    {
        $callCount        = new stdClass();
        $callCount->count = 0;
        $repository       = (new Repository())->setMainBranchName('master');
        $review           = (new CodeReview())->setTargetBranch('master')->setRepository($repository);
        $revisions        = [new Revision()];
        $options          = new FileDiffOptions(5, DiffComparePolicy::ALL);
        $branchName       = 'branch';

        $exception = $this->createMock(ProcessFailedException::class);

        $this->diffService->expects($this->once())->method('getDiffForBranch')
            ->with($review, $revisions, $branchName, $options)
            ->willThrowException($exception);

        $this->expectException(ProcessFailedException::class);
        $this->service->getDiffForBranch($review, $revisions, $branchName, $options);
    }
}

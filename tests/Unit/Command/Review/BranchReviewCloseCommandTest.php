<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Command\Review;

use DR\Review\Command\Review\BranchReviewCloseCommand;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Git\Branch\LockableGitBranchService;
use DR\Review\Service\Webhook\ReviewEventService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(BranchReviewCloseCommand::class)]
class BranchReviewCloseCommandTest extends AbstractTestCase
{
    private RepositoryRepository&MockObject     $repositoryRepository;
    private CodeReviewRepository&MockObject     $reviewRepository;
    private LockableGitBranchService&MockObject $branchService;
    private ReviewEventService&MockObject       $reviewEventService;
    private BranchReviewCloseCommand            $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryRepository = $this->createMock(RepositoryRepository::class);
        $this->reviewRepository     = $this->createMock(CodeReviewRepository::class);
        $this->branchService        = $this->createMock(LockableGitBranchService::class);
        $this->reviewEventService   = $this->createMock(ReviewEventService::class);
        $this->command              = new BranchReviewCloseCommand(
            $this->repositoryRepository,
            $this->reviewRepository,
            $this->branchService,
            $this->reviewEventService
        );
    }

    public function testExecuteNoOpenReviews(): void
    {
        $repository = new Repository();

        $this->repositoryRepository->expects($this->once())->method('findBy')->with(['active' => true])->willReturn([$repository]);
        $this->reviewRepository->expects($this->once())->method('findBy')
            ->with(['repository' => $repository, 'type' => 'branch', 'state' => 'open'])
            ->willReturn([]);
        $this->branchService->expects($this->never())->method('getRemoteBranches');

        (new CommandTester($this->command))->execute([]);
    }

    public function testExecuteReviewBranchStillExist(): void
    {
        $repository = new Repository();
        $review     = (new CodeReview())->setReferenceId('origin/branch');

        $this->repositoryRepository->expects($this->once())->method('findBy')->with(['active' => true])->willReturn([$repository]);
        $this->reviewRepository->expects($this->once())->method('findBy')
            ->with(['repository' => $repository, 'type' => 'branch', 'state' => 'open'])
            ->willReturn([$review]);
        $this->branchService->expects($this->once())->method('getRemoteBranches')->with($repository)->willReturn(['origin/branch']);
        $this->reviewRepository->expects($this->never())->method('save');

        (new CommandTester($this->command))->execute([]);
    }

    public function testExecuteCloseReview(): void
    {
        $repository = new Repository();
        $review     = (new CodeReview())->setState('open')->setReferenceId('origin/branch');

        $this->repositoryRepository->expects($this->once())->method('findBy')->with(['active' => true])->willReturn([$repository]);
        $this->reviewRepository->expects($this->once())->method('findBy')
            ->with(['repository' => $repository, 'type' => 'branch', 'state' => 'open'])
            ->willReturn([$review]);
        $this->branchService->expects($this->once())->method('getRemoteBranches')->with($repository)->willReturn([]);
        $this->reviewRepository->expects($this->once())->method('save')->with($review, true);
        $this->reviewEventService->expects($this->once())->method('reviewStateChanged')->with($review, 'open', null);

        (new CommandTester($this->command))->execute([]);
    }
}

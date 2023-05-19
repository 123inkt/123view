<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Exception\RepositoryException;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Git\Branch\GitBranchService;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModelProvider\ProjectBranchesViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(ProjectBranchesViewModelProvider::class)]
class ProjectBranchesViewModelProviderTest extends AbstractTestCase
{
    private GitBranchService&MockObject      $branchService;
    private CodeReviewRepository&MockObject  $reviewRepository;
    private ProjectBranchesViewModelProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->branchService    = $this->createMock(GitBranchService::class);
        $this->reviewRepository = $this->createMock(CodeReviewRepository::class);
        $this->provider         = new ProjectBranchesViewModelProvider($this->branchService, $this->reviewRepository);
    }

    /**
     * @throws RepositoryException
     */
    public function testGetProjectBranchesViewModel(): void
    {
        $repository = new Repository();
        $review     = new CodeReview();
        $review->setReferenceId('branch');

        $this->branchService->expects(self::exactly(2))
            ->method('getRemoteBranches')
            ->with($repository)
            ->willReturn(['branchA', 'mergedB'], ['mergedB']);
        $this->reviewRepository->expects(self::once())
            ->method('findBy')
            ->with(['repository' => $repository, 'type' => CodeReviewType::BRANCH, 'referenceId' => ['branchA', 'mergedB']])
            ->willReturn([$review]);

        $model = $this->provider->getProjectBranchesViewModel($repository);
        static::assertSame($repository, $model->repository);
        static::assertSame(['branchA', 'mergedB'], $model->branches);
        static::assertSame(['mergedB'], $model->mergedBranches);
        static::assertSame($review, $model->getReview('branch'));
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Exception\RepositoryException;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Git\Branch\CacheableGitBranchService;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModelProvider\ProjectBranchesViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(ProjectBranchesViewModelProvider::class)]
class ProjectBranchesViewModelProviderTest extends AbstractTestCase
{
    private CacheableGitBranchService&MockObject $branchService;
    private CodeReviewRepository&MockObject      $reviewRepository;
    private ProjectBranchesViewModelProvider     $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->branchService    = $this->createMock(CacheableGitBranchService::class);
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
            ->willReturn(['branchA', 'mergedBranchB', 'foobar'], ['mergedBranchB']);
        $this->reviewRepository->expects(self::once())
            ->method('findBy')
            ->with(['repository' => $repository, 'type' => CodeReviewType::BRANCH, 'referenceId' => ['branchA', 'mergedBranchB']])
            ->willReturn([$review]);

        $model = $this->provider->getProjectBranchesViewModel($repository, 'branch');
        static::assertSame($repository, $model->repository);
        static::assertSame(['branchA', 'mergedBranchB'], $model->branches);
        static::assertSame(['mergedBranchB'], $model->mergedBranches);
        static::assertSame($review, $model->getReview('branch'));
    }
}

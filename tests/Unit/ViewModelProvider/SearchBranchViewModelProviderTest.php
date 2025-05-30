<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Git\Branch\GitBranchService;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Search\SearchBranchViewModel;
use DR\Review\ViewModelProvider\SearchBranchViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(SearchBranchViewModelProvider::class)]
class SearchBranchViewModelProviderTest extends AbstractTestCase
{
    private RepositoryRepository&MockObject $repositoryRepository;
    private GitBranchService&MockObject     $branchService;
    private CodeReviewRepository&MockObject $reviewRepository;
    private SearchBranchViewModelProvider   $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryRepository = $this->createMock(RepositoryRepository::class);
        $this->branchService        = $this->createMock(GitBranchService::class);
        $this->reviewRepository     = $this->createMock(CodeReviewRepository::class);
        $this->provider             = new SearchBranchViewModelProvider($this->repositoryRepository, $this->branchService, $this->reviewRepository);
    }

    /**
     * @throws Throwable
     */
    public function testGetSearchBranchViewModel(): void
    {
        $repository = (new Repository())->setId(123);
        $review     = (new CodeReview())->setRepository($repository)->setReferenceId('branch');

        $this->repositoryRepository->expects($this->once())->method('findBy')->with(['active' => true])->willReturn([$repository]);
        $this->branchService->expects($this->once())->method('getRemoteBranches')->with($repository)->willReturn(['branch']);
        $this->reviewRepository->expects($this->once())->method('findBy')
            ->with(['type' => CodeReviewType::BRANCH, 'referenceId' => ['branch']])
            ->willReturn([$review]);

        $expected = new SearchBranchViewModel(
            [123 => ['branch']],
            [123 => $repository],
            [123 => ['branch' => $review]],
            'branch'
        );
        $actual   = $this->provider->getSearchBranchViewModel('branch');
        static::assertEquals($expected, $actual);
    }

    /**
     * @throws Throwable
     */
    public function testGetSearchBranchViewModelWithoutResults(): void
    {
        $this->repositoryRepository->expects($this->once())->method('findBy')->with(['active' => true])->willReturn([]);
        $this->branchService->expects($this->never())->method('getRemoteBranches');
        $this->reviewRepository->expects($this->never())->method('findBy');

        $expected = new SearchBranchViewModel([], [], [], 'branch');
        $actual   = $this->provider->getSearchBranchViewModel('branch');
        static::assertEquals($expected, $actual);
    }
}

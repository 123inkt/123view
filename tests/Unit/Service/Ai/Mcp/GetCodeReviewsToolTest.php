<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai\Mcp;

use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Model\Mcp\CodeReviewQuery;
use DR\Review\Model\Mcp\CodeReviewResult;
use DR\Review\Repository\Mcp\CodeReviewRepository;
use DR\Review\Service\Ai\Mcp\GetCodeReviewsTool;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(GetCodeReviewsTool::class)]
class GetCodeReviewsToolTest extends AbstractTestCase
{
    private CodeReviewRepository&MockObject $reviewRepository;
    private GetCodeReviewsTool              $tool;

    public function setUp(): void
    {
        parent::setUp();
        $this->reviewRepository = $this->createMock(CodeReviewRepository::class);
        $this->tool             = new GetCodeReviewsTool($this->reviewRepository);
    }

    public function testInvokeReturnsEmptyArrayWhenNoReviewsFound(): void
    {
        $this->reviewRepository->expects($this->once())
            ->method('findByFilters')
            ->with(new CodeReviewQuery(), 50)
            ->willReturn([]);

        static::assertSame([], ($this->tool)());
    }

    public function testInvokePassesFiltersToRepository(): void
    {
        $this->reviewRepository->expects($this->once())
            ->method('findByFilters')
            ->with(new CodeReviewQuery('login', 'feature/x', 'author@example.com', 'https://gitlab.com', CodeReviewStateType::OPEN), 50)
            ->willReturn([]);

        ($this->tool)('login', 'feature/x', 'author@example.com', 'https://gitlab.com', CodeReviewStateType::OPEN);
    }

    public function testInvokeReturnsMappedReviews(): void
    {
        $repository = new Repository();
        $repository->setName('my-repo');
        $repository->setDisplayName('My Repo');

        $review = new CodeReview();
        $review->setProjectId(42);
        $review->setTitle('Fix login bug');
        $review->setDescription('');
        $review->setState(CodeReviewStateType::OPEN);
        $review->setCreateTimestamp(1000);
        $review->setUpdateTimestamp(2000);
        $review->setRepository($repository);

        $this->reviewRepository->expects($this->once())
            ->method('findByFilters')
            ->with(new CodeReviewQuery(title: 'login'), 50)
            ->willReturn([$review]);

        $result = ($this->tool)(title: 'login');

        $expected = new CodeReviewResult(
            id:            42,
            title:         'Fix login bug',
            state:         CodeReviewStateType::OPEN,
            reviewerState: 'open',
            repository:    'My Repo',
            url:           'my-repo/app/reviews/42',
        );
        static::assertEquals([$expected], $result);
    }
}

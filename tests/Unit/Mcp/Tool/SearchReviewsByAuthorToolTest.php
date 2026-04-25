<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\Mcp\Tool;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Mcp\Tool\SearchReviewsByAuthorTool;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(SearchReviewsByAuthorTool::class)]
class SearchReviewsByAuthorToolTest extends AbstractTestCase
{
    private CodeReviewRepository&MockObject $reviewRepository;
    private SearchReviewsByAuthorTool       $tool;

    public function setUp(): void
    {
        parent::setUp();
        $this->reviewRepository = $this->createMock(CodeReviewRepository::class);
        $this->tool             = new SearchReviewsByAuthorTool($this->reviewRepository);
    }

    public function testInvokeReturnsEmptyArrayWhenNoReviewsFound(): void
    {
        $this->reviewRepository->expects($this->once())
            ->method('findByAuthorEmail')
            ->with('unknown@example.com')
            ->willReturn([]);

        $result = ($this->tool)('unknown@example.com');

        static::assertSame([], $result);
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
        $review->setState('open');
        $review->setCreateTimestamp(1000);
        $review->setUpdateTimestamp(2000);
        $review->setRepository($repository);

        $this->reviewRepository->expects($this->once())
            ->method('findByAuthorEmail')
            ->with('author@example.com')
            ->willReturn([$review]);

        $result = ($this->tool)('author@example.com');

        static::assertCount(1, $result);
        static::assertSame([
            'id'         => 42,
            'title'      => 'Fix login bug',
            'state'      => 'open',
            'repository' => 'My Repo',
            'url'        => 'my-repo/app/reviews/42',
        ], $result[0]);
    }
}

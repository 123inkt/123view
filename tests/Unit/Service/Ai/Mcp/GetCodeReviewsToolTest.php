<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai\Mcp;

use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Model\Mcp\CodeReviewQuery;
use DR\Review\Model\Mcp\CodeReviewResult;
use DR\Review\Repository\Mcp\CodeReviewRepository;
use DR\Review\Repository\Review\CodeReviewerRepository;
use DR\Review\Service\Ai\Mcp\GetCodeReviewsTool;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\RouterInterface;

#[CoversClass(GetCodeReviewsTool::class)]
class GetCodeReviewsToolTest extends AbstractTestCase
{
    private CodeReviewRepository&MockObject   $reviewRepository;
    private CodeReviewerRepository&MockObject $reviewerRepository;
    private RouterInterface&MockObject        $router;
    private GetCodeReviewsTool                $tool;

    public function setUp(): void
    {
        parent::setUp();
        $this->reviewRepository   = $this->createMock(CodeReviewRepository::class);
        $this->reviewerRepository = $this->createMock(CodeReviewerRepository::class);
        $this->router             = $this->createMock(RouterInterface::class);
        $this->tool               = new GetCodeReviewsTool($this->reviewRepository, $this->reviewerRepository, $this->router);
    }

    public function testInvokeReturnsEmptyArrayWhenNoReviewsFound(): void
    {
        $this->reviewRepository->expects($this->once())
            ->method('findByFilters')
            ->with(new CodeReviewQuery(), 50)
            ->willReturn([]);

        $this->reviewerRepository->expects($this->never())->method('findBy');
        $this->router->expects($this->never())->method('generate');

        static::assertSame([], ($this->tool)());
    }

    public function testInvokePassesFiltersToRepository(): void
    {
        $this->reviewRepository->expects($this->once())
            ->method('findByFilters')
            ->with(new CodeReviewQuery('login', 'feature/x', 'author@example.com', 'https://gitlab.com', CodeReviewStateType::OPEN), 50)
            ->willReturn([]);

        $this->reviewerRepository->expects($this->never())->method('findBy');
        $this->router->expects($this->never())->method('generate');

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

        $this->reviewerRepository->expects($this->once())->method('findBy')->with(['review' => [$review]]);

        $this->router->expects($this->once())
            ->method('generate')
            ->with(ReviewController::class, ['review' => $review], RouterInterface::ABSOLUTE_URL)
            ->willReturn('https://example.com/app/reviews/42');

        $result = ($this->tool)(title: 'login');

        $expected = new CodeReviewResult(
            id:            42,
            title:         'Fix login bug',
            state:         CodeReviewStateType::OPEN,
            reviewerState: 'open',
            repository:    'My Repo',
            url:           'https://example.com/app/reviews/42',
        );
        static::assertEquals([$expected], $result);
    }
}

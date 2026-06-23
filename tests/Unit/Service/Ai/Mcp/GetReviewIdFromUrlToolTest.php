<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai\Mcp;

use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Exception\Ai\InvalidReviewUrlException;
use DR\Review\Exception\Ai\RepositoryNotFoundException;
use DR\Review\Exception\Ai\ReviewNotFoundForUrlException;
use DR\Review\Model\Mcp\CodeReviewResult;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Mcp\CodeReviewRepository;
use DR\Review\Service\Ai\Mcp\GetReviewIdFromUrlTool;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(GetReviewIdFromUrlTool::class)]
class GetReviewIdFromUrlToolTest extends AbstractTestCase
{
    private RepositoryRepository&MockObject $repositoryRepository;
    private CodeReviewRepository&MockObject $reviewRepository;
    private GetReviewIdFromUrlTool          $tool;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryRepository = $this->createMock(RepositoryRepository::class);
        $this->reviewRepository     = $this->createMock(CodeReviewRepository::class);
        $this->tool                 = new GetReviewIdFromUrlTool($this->repositoryRepository, $this->reviewRepository);
    }

    public function testInvokeReturnsMappedReviewOnMatch(): void
    {
        $repository = new Repository();
        $repository->setName('my-repo');
        $repository->setDisplayName('My Repo');

        $review = new CodeReview();
        $review->setId(123);
        $review->setProjectId(42);
        $review->setTitle('Fix login bug');
        $review->setState(CodeReviewStateType::OPEN);
        $review->setRepository($repository);

        $this->repositoryRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => 'my-repo'])
            ->willReturn($repository);

        $this->reviewRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['repository' => $repository, 'projectId' => 42])
            ->willReturn($review);

        $result = ($this->tool)('https://example.com/app/my-repo/review/cr-42');

        $expected = new CodeReviewResult(
            id           : 123,
            title        : 'Fix login bug',
            state        : CodeReviewStateType::OPEN,
            reviewerState: $review->getReviewersState(),
            repository   : 'My Repo'
        );
        static::assertEquals($expected, $result);
    }

    public function testInvokeParsesBarePathUrl(): void
    {
        $repository = new Repository();
        $repository->setName('my-repo');
        $repository->setDisplayName('My Repo');

        $review = new CodeReview();
        $review->setId(7);
        $review->setProjectId(99);
        $review->setTitle('Bare path');
        $review->setState(CodeReviewStateType::OPEN);
        $review->setRepository($repository);

        $this->repositoryRepository->method('findOneBy')->with(['name' => 'my-repo'])->willReturn($repository);
        $this->reviewRepository->method('findOneBy')->with(['repository' => $repository, 'projectId' => 99])->willReturn($review);

        $result = ($this->tool)('/app/my-repo/review/cr-99');

        static::assertSame(7, $result->id);
    }

    #[DataProvider('malformedUrlProvider')]
    public function testInvokeThrowsOnMalformedUrl(string $url): void
    {
        $this->repositoryRepository->expects($this->never())->method('findOneBy');
        $this->reviewRepository->expects($this->never())->method('findOneBy');

        $this->expectException(InvalidReviewUrlException::class);
        ($this->tool)($url);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function malformedUrlProvider(): array
    {
        return [
            'empty string'        => [''],
            'no cr prefix'        => ['https://example.com/app/my-repo/review/42'],
            'missing review path' => ['https://example.com/app/my-repo/cr-42'],
            'non-numeric id'      => ['https://example.com/app/my-repo/review/cr-abc'],
            'uppercase repo'      => ['https://example.com/app/MyRepo/review/cr-42'],
        ];
    }

    public function testInvokeThrowsWhenRepositoryNotFound(): void
    {
        $this->repositoryRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => 'my-repo'])
            ->willReturn(null);
        $this->reviewRepository->expects($this->never())->method('findOneBy');

        $this->expectException(RepositoryNotFoundException::class);
        ($this->tool)('https://example.com/app/my-repo/review/cr-42');
    }

    public function testInvokeThrowsWhenReviewNotFound(): void
    {
        $repository = new Repository();
        $repository->setName('my-repo');
        $repository->setDisplayName('My Repo');

        $this->repositoryRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => 'my-repo'])
            ->willReturn($repository);

        $this->reviewRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['repository' => $repository, 'projectId' => 42])
            ->willReturn(null);

        $this->expectException(ReviewNotFoundForUrlException::class);
        ($this->tool)('https://example.com/app/my-repo/review/cr-42');
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Service\Ai\AiCodeReviewFileFilter;
use DR\Review\Service\Ai\AiCodeReviewService;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\MessageBag;

#[CoversClass(AiCodeReviewService::class)]
class AiCodeReviewServiceTest extends AbstractTestCase
{
    private ReviewDiffServiceInterface&MockObject   $diffService;
    private CodeReviewRevisionService&MockObject    $revisionService;
    private AgentInterface&MockObject               $agent;
    private AiCodeReviewFileFilter&MockObject       $fileFilter;
    private AiCodeReviewService                     $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->diffService     = $this->createMock(ReviewDiffServiceInterface::class);
        $this->revisionService = $this->createMock(CodeReviewRevisionService::class);
        $this->agent           = $this->createMock(AgentInterface::class);
        $this->fileFilter      = $this->createMock(AiCodeReviewFileFilter::class);
        $this->service         = new AiCodeReviewService(
            $this->logger,
            $this->diffService,
            $this->revisionService,
            $this->agent,
            $this->fileFilter
        );
    }

    public function testStartCodeReviewShouldReturnSuccessOnHappyFlow(): void
    {
        $repository = new Repository();
        $revision   = (new Revision())->setRepository($repository);
        $review     = (new CodeReview())->setId(123)->setRepository($repository)->setType(CodeReviewType::COMMITS);

        $diffFile      = new DiffFile();
        $diffFile->raw = 'diff content';

        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->diffService->expects($this->once())
            ->method('getDiffForRevisions')
            ->with($repository, [$revision], self::isInstanceOf(FileDiffOptions::class))
            ->willReturn([$diffFile]);
        $this->fileFilter->expects($this->once())->method('__invoke')->with($diffFile)->willReturn(true);
        $this->agent->expects($this->once())->method('call')->with(self::isInstanceOf(MessageBag::class));

        $result = $this->service->startCodeReview($review);

        static::assertSame(AiCodeReviewService::RESULT_SUCCESS, $result);
    }

    public function testStartCodeReviewShouldReturnNoFilesWhenAllFilesFiltered(): void
    {
        $repository = new Repository();
        $revision   = (new Revision())->setRepository($repository);
        $review     = (new CodeReview())->setId(456)->setRepository($repository)->setType(CodeReviewType::COMMITS);

        $diffFile = new DiffFile();

        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->diffService->expects($this->once())
            ->method('getDiffForRevisions')
            ->with($repository, [$revision], self::isInstanceOf(FileDiffOptions::class))
            ->willReturn([$diffFile]);
        $this->fileFilter->expects($this->once())->method('__invoke')->with($diffFile)->willReturn(false);
        $this->agent->expects($this->never())->method('call');

        $result = $this->service->startCodeReview($review);

        static::assertSame(AiCodeReviewService::RESULT_NO_FILES, $result);
    }

    public function testStartCodeReviewShouldReturnFailureWhenAgentThrowsException(): void
    {
        $repository = new Repository();
        $revision   = (new Revision())->setRepository($repository);
        $review     = (new CodeReview())->setId(789)->setRepository($repository)->setType(CodeReviewType::COMMITS);

        $diffFile      = new DiffFile();
        $diffFile->raw = 'diff content';

        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->diffService->expects($this->once())
            ->method('getDiffForRevisions')
            ->with($repository, [$revision], self::isInstanceOf(FileDiffOptions::class))
            ->willReturn([$diffFile]);
        $this->fileFilter->expects($this->once())->method('__invoke')->with($diffFile)->willReturn(true);
        $this->agent->expects($this->once())->method('call')->willThrowException(new RuntimeException('Agent error'));

        $result = $this->service->startCodeReview($review);

        static::assertSame(AiCodeReviewService::RESULT_FAILURE, $result);
    }

    public function testStartCodeReviewBranchReview(): void
    {
        $repository = new Repository();
        $review     = (new CodeReview())->setId(123)->setRepository($repository)->setType(CodeReviewType::BRANCH)->setReferenceId('feature-branch');

        $diffFile      = new DiffFile();
        $diffFile->raw = 'diff content';

        $this->revisionService->expects($this->never())->method('getRevisions');
        $this->diffService->expects($this->once())
            ->method('getDiffForBranch')
            ->with($review, [], 'feature-branch', self::isInstanceOf(FileDiffOptions::class))
            ->willReturn([$diffFile]);
        $this->fileFilter->expects($this->once())->method('__invoke')->with($diffFile)->willReturn(true);
        $this->agent->expects($this->once())->method('call')->with(self::isInstanceOf(MessageBag::class));

        $result = $this->service->startCodeReview($review);

        static::assertSame(AiCodeReviewService::RESULT_SUCCESS, $result);
    }
}

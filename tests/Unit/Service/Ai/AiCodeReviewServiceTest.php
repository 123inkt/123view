<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Service\Ai\AiCodeReviewFileFilter;
use DR\Review\Service\Ai\AiCodeReviewService;
use DR\Review\Service\CodeReview\CodeReviewDiffService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\MessageBag;
use Throwable;

#[CoversClass(AiCodeReviewService::class)]
class AiCodeReviewServiceTest extends AbstractTestCase
{
    private CodeReviewDiffService&MockObject  $diffService;
    private AgentInterface&MockObject         $agent;
    private AiCodeReviewFileFilter&MockObject $fileFilter;
    private AiCodeReviewService               $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->diffService = $this->createMock(CodeReviewDiffService::class);
        $this->agent       = $this->createMock(AgentInterface::class);
        $this->fileFilter  = $this->createMock(AiCodeReviewFileFilter::class);
        $this->service     = new AiCodeReviewService($this->logger, $this->diffService, $this->agent, $this->fileFilter);
    }

    /**
     * @throws Throwable
     */
    public function testStartCodeReviewShouldReturnSuccessOnHappyFlow(): void
    {
        $review = new CodeReview()->setId(123);

        $diffFile      = new DiffFile();
        $diffFile->raw = 'diff content';

        $this->diffService->expects($this->once())->method('getDiff')->with($review)->willReturn([$diffFile]);
        $this->fileFilter->expects($this->once())->method('__invoke')->with($diffFile)->willReturn(true);
        $this->agent->expects($this->once())->method('call')->with(self::isInstanceOf(MessageBag::class));

        $result = $this->service->startCodeReview($review);

        static::assertSame(AiCodeReviewService::RESULT_SUCCESS, $result);
    }

    /**
     * @throws Throwable
     */
    public function testStartCodeReviewShouldReturnNoFilesWhenAllFilesFiltered(): void
    {
        $review   = new CodeReview()->setId(456);
        $diffFile = new DiffFile();

        $this->diffService->expects($this->once())->method('getDiff')->with($review)->willReturn([$diffFile]);
        $this->fileFilter->expects($this->once())->method('__invoke')->with($diffFile)->willReturn(false);
        $this->agent->expects($this->never())->method('call');

        $result = $this->service->startCodeReview($review);

        static::assertSame(AiCodeReviewService::RESULT_NO_FILES, $result);
    }

    /**
     * @throws Throwable
     */
    public function testStartCodeReviewShouldReturnFailureWhenAgentThrowsException(): void
    {
        $review = new CodeReview()->setId(789);

        $diffFile      = new DiffFile();
        $diffFile->raw = 'diff content';

        $this->diffService->expects($this->once())->method('getDiff')->with($review)->willReturn([$diffFile]);
        $this->fileFilter->expects($this->once())->method('__invoke')->with($diffFile)->willReturn(true);
        $this->agent->expects($this->once())->method('call')->willThrowException(new RuntimeException('Agent error'));

        $result = $this->service->startCodeReview($review);

        static::assertSame(AiCodeReviewService::RESULT_FAILURE, $result);
    }
}

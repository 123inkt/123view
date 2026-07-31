<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Service\Ai\AiCodeReviewCoverageReporter;
use DR\Review\Service\Ai\AiCodeReviewFileFilter;
use DR\Review\Service\Ai\AiCodeReviewFileSkipReason;
use DR\Review\Service\Ai\AiCodeReviewService;
use DR\Review\Service\Ai\AiReviewInstructionProvider;
use DR\Review\Service\Ai\Reference\AiReviewReferenceBudgetTracker;
use DR\Review\Service\CodeReview\CodeReviewDiffService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Result\ResultInterface;
use Throwable;

#[CoversClass(AiCodeReviewService::class)]
class AiCodeReviewServiceTest extends AbstractTestCase
{
    private CodeReviewDiffService&MockObject         $diffService;
    private AgentInterface&MockObject                $agent;
    private AiCodeReviewFileFilter&MockObject         $fileFilter;
    private AiReviewInstructionProvider&MockObject    $instructionProvider;
    private AiReviewReferenceBudgetTracker&MockObject $budgetTracker;
    private AiCodeReviewCoverageReporter&MockObject   $coverageReporter;
    private AiCodeReviewService                      $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->diffService         = $this->createMock(CodeReviewDiffService::class);
        $this->agent                = $this->createMock(AgentInterface::class);
        $this->fileFilter           = $this->createMock(AiCodeReviewFileFilter::class);
        $this->instructionProvider  = $this->createMock(AiReviewInstructionProvider::class);
        $this->budgetTracker        = $this->createMock(AiReviewReferenceBudgetTracker::class);
        $this->coverageReporter     = $this->createMock(AiCodeReviewCoverageReporter::class);
        $this->service = new AiCodeReviewService(
            $this->logger,
            $this->diffService,
            $this->agent,
            $this->fileFilter,
            $this->instructionProvider,
            $this->budgetTracker,
            $this->coverageReporter
        );
    }

    private function createDiffFile(string $path, string $raw = 'diff content'): DiffFile
    {
        $file               = new DiffFile();
        $file->filePathAfter = $path;
        $file->raw           = $raw;

        return $file;
    }

    /**
     * @throws Throwable
     */
    public function testStartCodeReviewShouldReturnNoFilesWhenAllFilesFiltered(): void
    {
        $review = new CodeReview()->setId(456);
        $file   = $this->createDiffFile('vendor/lib.lock');

        $this->diffService->expects($this->once())->method('getDiff')->with($review)->willReturn([$file]);
        $this->fileFilter->expects($this->once())->method('getSkipReason')->with($file)->willReturn(AiCodeReviewFileSkipReason::Irrelevant);
        $this->instructionProvider->expects($this->never())->method('getSystemPrompt');
        $this->agent->expects($this->never())->method('call');
        $this->coverageReporter->expects($this->never())->method('reportIncompleteCoverage');
        $this->budgetTracker->expects($this->never())->method('startSession');

        $result = $this->service->startCodeReview($review);

        static::assertSame(AiCodeReviewService::RESULT_NO_FILES, $result);
    }

    /**
     * @throws Throwable
     */
    public function testStartCodeReviewShouldReturnSuccessOnHappyFlow(): void
    {
        $review = new CodeReview()->setId(123);
        $file   = $this->createDiffFile('src/Foo.php');

        $this->diffService->expects($this->once())->method('getDiff')->with($review)->willReturn([$file]);
        $this->fileFilter->expects($this->once())->method('getSkipReason')->with($file)->willReturn(null);
        $this->instructionProvider->expects($this->once())->method('getSystemPrompt')->willReturn('system prompt');
        $this->budgetTracker->expects($this->once())->method('startSession')->with('123:src/Foo.php');
        $this->budgetTracker->expects($this->once())->method('endSession')->with('123:src/Foo.php');
        $this->agent->expects($this->once())->method('call')->with(self::isInstanceOf(MessageBag::class));
        $this->coverageReporter->expects($this->never())->method('reportIncompleteCoverage');

        $result = $this->service->startCodeReview($review);

        static::assertSame(AiCodeReviewService::RESULT_SUCCESS, $result);
    }

    /**
     * @throws Throwable
     */
    public function testStartCodeReviewShouldReturnPartialWhenFileSkippedForSize(): void
    {
        $review     = new CodeReview()->setId(789);
        $acceptedFile = $this->createDiffFile('src/Foo.php');
        $tooLargeFile = $this->createDiffFile('src/Big.php');

        $this->diffService->expects($this->once())->method('getDiff')->with($review)->willReturn([$acceptedFile, $tooLargeFile]);
        $this->fileFilter->expects($this->exactly(2))->method('getSkipReason')->willReturnMap([
            [$acceptedFile, null],
            [$tooLargeFile, AiCodeReviewFileSkipReason::TooLarge],
        ]);
        $this->instructionProvider->expects($this->once())->method('getSystemPrompt')->willReturn('system prompt');
        $this->budgetTracker->expects($this->once())->method('startSession')->with('789:src/Foo.php');
        $this->budgetTracker->expects($this->once())->method('endSession')->with('789:src/Foo.php');
        $this->agent->expects($this->once())->method('call');
        $this->coverageReporter->expects($this->once())->method('reportIncompleteCoverage')
            ->with($review, ['src/Big.php'], [], 1);

        $result = $this->service->startCodeReview($review);

        static::assertSame(AiCodeReviewService::RESULT_PARTIAL, $result);
    }

    /**
     * @throws Throwable
     */
    public function testStartCodeReviewShouldReturnFailureWhenTheOnlyFileFailsToReview(): void
    {
        $review = new CodeReview()->setId(321);
        $file   = $this->createDiffFile('src/Foo.php');

        $this->diffService->expects($this->once())->method('getDiff')->with($review)->willReturn([$file]);
        $this->fileFilter->expects($this->once())->method('getSkipReason')->willReturn(null);
        $this->instructionProvider->expects($this->once())->method('getSystemPrompt')->willReturn('system prompt');
        $this->budgetTracker->expects($this->once())->method('startSession')->with('321:src/Foo.php');
        $this->agent->expects($this->once())->method('call')->willThrowException(new RuntimeException('Agent error'));
        $this->budgetTracker->expects($this->once())->method('endSession')->with('321:src/Foo.php');
        $this->coverageReporter->expects($this->once())->method('reportIncompleteCoverage')->with($review, [], ['src/Foo.php'], 0);

        $result = $this->service->startCodeReview($review);

        static::assertSame(AiCodeReviewService::RESULT_FAILURE, $result);
    }

    /**
     * @throws Throwable
     */
    public function testStartCodeReviewShouldContinueAndReturnPartialWhenOneOfMultipleFilesFails(): void
    {
        $review = new CodeReview()->setId(654);
        $file1  = $this->createDiffFile('src/Foo.php');
        $file2  = $this->createDiffFile('src/Bar.php');

        $this->diffService->expects($this->once())->method('getDiff')->with($review)->willReturn([$file1, $file2]);
        $this->fileFilter->expects($this->exactly(2))->method('getSkipReason')->willReturn(null);
        $this->instructionProvider->expects($this->once())->method('getSystemPrompt')->willReturn('system prompt');
        $this->budgetTracker->expects($this->exactly(2))->method('startSession');
        $this->budgetTracker->expects($this->exactly(2))->method('endSession');

        $counter       = new class {
            public int $count = 0;
        };
        $successResult = static::createStub(ResultInterface::class);
        $this->agent->expects($this->exactly(2))->method('call')->willReturnCallback(static function () use ($counter, $successResult) {
            $counter->count++;
            if ($counter->count === 1) {
                throw new RuntimeException('Agent error');
            }

            return $successResult;
        });

        $this->coverageReporter->expects($this->once())->method('reportIncompleteCoverage')->with($review, [], ['src/Foo.php'], 1);

        $result = $this->service->startCodeReview($review);

        static::assertSame(AiCodeReviewService::RESULT_PARTIAL, $result);
    }
}

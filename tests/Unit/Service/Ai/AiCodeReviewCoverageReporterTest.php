<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Message\Review\AiReviewCoverageIncomplete;
use DR\Review\Service\Ai\AiCodeReviewCoverageReporter;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(AiCodeReviewCoverageReporter::class)]
class AiCodeReviewCoverageReporterTest extends AbstractTestCase
{
    private MessageBusInterface&MockObject $messageBus;
    private AiCodeReviewCoverageReporter   $reporter;

    public function setUp(): void
    {
        parent::setUp();
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->reporter    = new AiCodeReviewCoverageReporter($this->messageBus);
    }

    public function testReportIncompleteCoverageShouldDoNothingWhenNothingSkippedOrFailed(): void
    {
        $review = new CodeReview()->setId(1);

        $this->messageBus->expects($this->never())->method('dispatch');

        $this->reporter->reportIncompleteCoverage($review, [], [], 3);
    }

    public function testReportIncompleteCoverageShouldDispatchMessageWhenFilesSkipped(): void
    {
        $review = new CodeReview()->setId(1);

        $this->messageBus->expects($this->once())->method('dispatch')
            ->with(self::callback(static function (AiReviewCoverageIncomplete $message) {
                return $message->reviewId === 1 && $message->skippedFiles === ['a.php'] && $message->failedFiles === [] && $message->reviewedFileCount === 2;
            }))
            ->willReturn(new Envelope(new AiReviewCoverageIncomplete(1, ['a.php'], [], 2)));

        $this->reporter->reportIncompleteCoverage($review, ['a.php'], [], 2);
    }
}

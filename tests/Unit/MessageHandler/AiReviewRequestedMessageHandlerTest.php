<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler;

use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Message\Review\AiReviewRequested;
use DR\Review\MessageHandler\AiReviewRequestedMessageHandler;
use DR\Review\Model\Mercure\UpdateMessage;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Ai\AiCodeReviewService;
use DR\Review\Service\Mercure\MessagePublisher;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(AiReviewRequestedMessageHandler::class)]
class AiReviewRequestedMessageHandlerTest extends AbstractTestCase
{
    private CodeReviewRepository&MockObject  $reviewRepository;
    private AiCodeReviewService&MockObject   $codeReview;
    private MessagePublisher&MockObject      $messagePublisher;
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private TranslatorInterface&MockObject   $translator;
    private AiReviewRequestedMessageHandler  $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reviewRepository = $this->createMock(CodeReviewRepository::class);
        $this->codeReview       = $this->createMock(AiCodeReviewService::class);
        $this->messagePublisher = $this->createMock(MessagePublisher::class);
        $this->urlGenerator     = $this->createMock(UrlGeneratorInterface::class);
        $this->translator       = $this->createMock(TranslatorInterface::class);
        $this->handler          = new AiReviewRequestedMessageHandler(
            $this->reviewRepository,
            $this->codeReview,
            $this->messagePublisher,
            $this->urlGenerator,
            $this->translator
        );
        $this->handler->setLogger($this->logger);
    }

    public function testInvokeReviewNotFound(): void
    {
        $message = new AiReviewRequested(123);

        $this->reviewRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->codeReview->expects($this->never())->method('startCodeReview');
        $this->messagePublisher->expects($this->never())->method('publishToReview');
        $this->urlGenerator->expects($this->never())->method('generate');
        $this->translator->expects($this->never())->method('trans');

        ($this->handler)($message);
    }

    public function testInvokeReviewFound(): void
    {
        $message = new AiReviewRequested(123);
        $review  = new CodeReview();
        $review->setId(123);

        $this->reviewRepository->expects($this->once())->method('find')->with(123)->willReturn($review);
        $this->codeReview->expects($this->once())->method('startCodeReview')->with($review)->willReturn(AiCodeReviewService::RESULT_SUCCESS);
        $this->translator->expects($this->once())->method('trans')
            ->with('ai.review.completed.success')
            ->willReturn('The AI code review completed successfully');
        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(ReviewController::class, ['review' => $review], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('https://example.com/review/123');
        $this->messagePublisher->expects($this->once())
            ->method('publishToReview')
            ->with(self::isInstanceOf(UpdateMessage::class), $review);

        ($this->handler)($message);
    }

    public function testInvokeReviewFoundWithFailure(): void
    {
        $message = new AiReviewRequested(123);
        $review  = new CodeReview();
        $review->setId(123);

        $this->reviewRepository->expects($this->once())->method('find')->with(123)->willReturn($review);
        $this->codeReview->expects($this->once())->method('startCodeReview')->with($review)->willReturn(AiCodeReviewService::RESULT_FAILURE);
        $this->translator->expects($this->once())->method('trans')
            ->with('ai.review.completed.failure')
            ->willReturn('The AI code review failed');
        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(ReviewController::class, ['review' => $review], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('https://example.com/review/123');
        $this->messagePublisher->expects($this->once())
            ->method('publishToReview')
            ->with(self::isInstanceOf(UpdateMessage::class), $review);

        ($this->handler)($message);
    }

    public function testInvokeReviewFoundWithNoFiles(): void
    {
        $message = new AiReviewRequested(123);
        $review  = new CodeReview();
        $review->setId(123);

        $this->reviewRepository->expects($this->once())->method('find')->with(123)->willReturn($review);
        $this->codeReview->expects($this->once())->method('startCodeReview')->with($review)->willReturn(AiCodeReviewService::RESULT_NO_FILES);
        $this->translator->expects($this->once())->method('trans')
            ->with('ai.review.completed.no.files')
            ->willReturn('No files to review');
        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(ReviewController::class, ['review' => $review], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('https://example.com/review/123');
        $this->messagePublisher->expects($this->once())
            ->method('publishToReview')
            ->with(self::isInstanceOf(UpdateMessage::class), $review);

        ($this->handler)($message);
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler;

use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Message\Review\AiReviewRequested;
use DR\Review\Model\Mercure\UpdateMessage;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Ai\AiCodeReviewService;
use DR\Review\Service\Mercure\MessagePublisher;
use League\Uri\Http;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;

class AiReviewRequestedMessageHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly CodeReviewRepository $reviewRepository,
        private readonly AiCodeReviewService $codeReview,
        private readonly MessagePublisher $messagePublisher,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @throws Throwable
     */
    #[AsMessageHandler(fromTransport: 'async_ai_review')]
    public function __invoke(AiReviewRequested $message): void
    {
        $this->logger?->info("AiReviewRequestedMessageHandler: review: {id}", ['id' => $message->reviewId]);

        $review = $this->reviewRepository->find($message->reviewId);
        if ($review === null) {
            return;
        }

        $success       = $this->codeReview->startCodeReview($review);
        $resultMessage = match ($success) {
            AiCodeReviewService::RESULT_FAILURE  => "The AI code review completed unsuccessfully",
            AiCodeReviewService::RESULT_NO_FILES => "No suitable files found for AI code review",
            default                              => "The AI code review completed successfully",
        };

        // send mercure message
        $url           = $this->urlGenerator->generate(ReviewController::class, ['review' => $review], UrlGeneratorInterface::ABSOLUTE_URL);
        $updateMessage = new UpdateMessage(
            1,
            0,
            (int)$review->getId(),
            'ai-review-completed',
            'Claude Sonnet 4.5',
            $resultMessage,
            Http::new($url)
        );
        $this->messagePublisher->publishToReview($updateMessage, $review);
    }
}

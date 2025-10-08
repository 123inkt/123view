<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler;

use DR\Review\Message\Review\AiReviewRequested;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Api\Anthropic\AnthropicCodeReview;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

class AiReviewRequestedMessageHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly CodeReviewRepository $reviewRepository,
        private readonly AnthropicCodeReview $codeReview
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

        $this->codeReview->requestCodeReview($review);
    }
}

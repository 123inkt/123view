<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler;

use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Message\Review\AiReviewRequested;
use DR\Review\Model\Mercure\UpdateMessage;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\Api\Anthropic\AnthropicCodeReview;
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
        private readonly AnthropicCodeReview $codeReview,
        private readonly UserRepository $userRepository,
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

        $success = $this->codeReview->requestCodeReview($review);

        $url     = $this->urlGenerator->generate(ReviewController::class, ['reviewId' => $review->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $message = new UpdateMessage(
            1,
            (int)$message->getUserId(),
            (int)$review->getId(),
            'ai-review-completed',
            'Claude Sonnet 4.5',
            $success ? 'Code review completed' : 'Code review completed without any suggestions',
            Http::new($url)
        );
        $this->messagePublisher->publishToReview($message, $review);
    }
}

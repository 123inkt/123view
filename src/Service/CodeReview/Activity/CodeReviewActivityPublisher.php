<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Activity;

use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Utility\Assert;
use Nette\Utils\Json;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Throwable;

class CodeReviewActivityPublisher implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly CodeReviewActivityFormatter $activityFormatter,
        private readonly UserRepository $userRepository,
        private readonly CodeReviewActivityUrlGenerator $urlGenerator,
        private readonly HubInterface $mercureHub
    ) {
    }

    /**
     * @throws Throwable
     */
    public function publish(CodeReviewActivity $activity): void
    {
        $message = $this->activityFormatter->format($activity);
        if ($message === null) {
            return;
        }

        $review     = Assert::notNull($activity->getReview());
        $repository = Assert::notNull($review->getRepository());
        $userId     = (int)$activity->getUser()?->getId();

        // create the payload
        $payload = [
            'eventId'   => $activity->getId(),
            'userId'    => $userId,
            'reviewId'  => $review->getId(),
            'eventName' => $activity->getEventName(),
            'title'     => sprintf('CR-%s - %s', $review->getProjectId(), $repository->getDisplayName()),
            'message'   => $message,
            'url'       => $this->urlGenerator->generate($activity)
        ];

        // gather topics
        $topics = [sprintf('/review/%d', $review->getId())];
        if (count($review->getActors()) > 0) {
            foreach ($this->userRepository->findBy(['id' => $review->getActors()]) as $actor) {
                if ($actor->getId() !== $userId && $actor->getSetting()->hasBrowserNotificationEvent((string)$activity->getEventName())) {
                    $topics[] = sprintf('/user/%d', (int)$actor->getId());
                }
            }
        }

        foreach ($topics as $topic) {
            $this->logger?->info('Mercure publish: `' . $topic . '` with message: ' . $message);

            // publish to mercure
            $this->mercureHub->publish(new Update($topic, Json::encode(['topic' => $topic] + $payload), true));
        }
    }
}

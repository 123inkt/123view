<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Activity;

use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Review\Repository\User\UserRepository;
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

        $reviewId = (int)$activity->getReview()?->getId();
        $userId   = (int)$activity->getUser()?->getId();

        // gather topics
        $topics = [sprintf('/review/%d', $reviewId)];
        foreach ($this->userRepository->getActors($reviewId) as $actor) {
            if ($actor->getId() !== $userId && $actor->getSetting()->hasBrowserNotificationEvent($activity->getEventName())) {
                $topics[] = [sprintf('/user/%d', (int)$actor->getId())];
            }
        }

        // create the payload
        $payload = [
            'userId'    => $userId,
            'reviewId'  => (int)$activity->getReview()?->getId(),
            'eventName' => $activity->getEventName(),
            'message'   => $message
        ];

        $this->logger->info('Mercure publish: `' . implode(' ', $topics) . '` with message: ' . $message);

        // publish to mercure
        $update = new Update($topics, Json::encode($payload), true);
        $this->mercureHub->publish($update);
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Service\Mercure;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\User\User;
use DR\Review\Model\Mercure\UpdateMessage;
use DR\Utils\Arrays;
use Nette\Utils\Json;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Throwable;

class MessagePublisher implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly HubInterface $mercureHub)
    {
    }

    /**
     * @throws Throwable
     */
    public function publishToReview(UpdateMessage $message, CodeReview $review): void
    {
        // create topic
        $topic = sprintf('/review/%d', $review->getId());

        $this->logger?->info('Mercure publish: `' . $topic . '` with message: ' . $message->message);

        // publish to mercure
        $this->mercureHub->publish(new Update($topic, Json::encode(['topic' => $topic] + $message->jsonSerialize()), true));
    }

    /**
     * @param User|User[] $users
     *
     * @throws Throwable
     */
    public function publishToUsers(UpdateMessage $message, User|array $users): void
    {
        foreach (Arrays::wrap($users) as $user) {
            $topic = sprintf('/user/%d', $user->getId());

            $this->logger?->info('Mercure publish: `' . $topic . '` with message: ' . $message->message);

            // publish to mercure
            $this->mercureHub->publish(new Update($topic, Json::encode(['topic' => $topic] + $message->jsonSerialize()), true));
        }
    }
}

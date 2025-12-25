<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Activity;

use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Review\Model\Mercure\UpdateMessage;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\Mercure\MessagePublisher;
use DR\Utils\Assert;
use League\Uri\Http;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Throwable;

class CodeReviewActivityPublisher implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly CodeReviewActivityFormatter $activityFormatter,
        private readonly UserRepository $userRepository,
        private readonly CodeReviewActivityUrlGenerator $urlGenerator,
        private readonly MessagePublisher $messagePublisher
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
        $updateMessage = new UpdateMessage(
            (int)$activity->getId(),
            $userId,
            (int)$review->getId(),
            $activity->getEventName(),
            sprintf(
                'CR-%s - %s - %s',
                $review->getProjectId(),
                $repository->getDisplayName(),
                mb_substr((string)$review->getTitle(), 0, 100)
            ),
            $message,
            Http::new($this->urlGenerator->generate($activity))
        );

        $this->messagePublisher->publishToReview($updateMessage, $review);

        $users = [];
        if (count($review->getActors()) > 0) {
            foreach ($this->userRepository->findBy(['id' => $review->getActors()]) as $actor) {
                if ($actor->getId() !== $userId && $actor->getSetting()->hasBrowserNotificationEvent($activity->getEventName())) {
                    $users[] = $actor;
                }
            }
        }

        $this->messagePublisher->publishToUsers($updateMessage, $users);
    }
}

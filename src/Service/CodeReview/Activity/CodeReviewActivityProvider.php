<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Activity;

use Doctrine\DBAL\Exception;
use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Review\Message\CodeReviewAwareInterface;
use DR\Review\Message\UserAwareInterface;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\User\UserRepository;

class CodeReviewActivityProvider
{
    public function __construct(private readonly CodeReviewRepository $reviewRepository, private readonly UserRepository $userRepository)
    {
    }

    /**
     * @throws Exception
     */
    public function fromEvent(CodeReviewAwareInterface $event): ?CodeReviewActivity
    {
        $review = $this->reviewRepository->find($event->getReviewId());
        if ($review === null) {
            return null;
        }

        $activity = new CodeReviewActivity();
        $activity->setReview($review);
        $activity->setCreateTimestamp(time());
        $activity->setEventName($event->getName());
        $activity->setData($event->getPayload());

        if ($event instanceof UserAwareInterface && $event->getUserId() !== null) {
            $activity->setUser($this->userRepository->find($event->getUserId()));
        }

        $actorUserIds = array_map(static fn($user) => $user->getId(), $this->userRepository->getActors((int)$review->getId()));
        if ($event->getUserId() !== null && in_array($event->getUserId(), $actorUserIds, true) === false) {
            $actorUserIds[] = $event->getUserId();
        }
        $activity->setRelevantToUsers($actorUserIds);

        return $activity;
    }
}

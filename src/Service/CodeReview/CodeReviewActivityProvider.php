<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview;

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

        return $activity;
    }
}

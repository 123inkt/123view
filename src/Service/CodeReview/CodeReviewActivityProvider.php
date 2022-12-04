<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\CodeReview;

use DR\GitCommitNotification\Entity\Review\CodeReviewActivity;
use DR\GitCommitNotification\Message\CodeReviewAwareInterface;
use DR\GitCommitNotification\Message\UserAwareInterface;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Repository\User\UserRepository;

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

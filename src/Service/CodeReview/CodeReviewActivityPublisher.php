<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview;

use DR\Review\Entity\Review\CodeReviewActivity;
use JsonException;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class CodeReviewActivityPublisher
{
    public function __construct(private readonly CodeReviewActivityFormatter $activityFormatter, private readonly HubInterface $mercureHub)
    {
    }

    /**
     * @throws JsonException
     */
    public function publish(CodeReviewActivity $activity): void
    {
        $payload = [
            'userId'    => (int)$activity->getUser()?->getId(),
            'reviewId'  => (int)$activity->getReview()?->getId(),
            'eventName' => $activity->getEventName(),
            'message'   => $this->activityFormatter->format($activity)
        ];

        // publish to mercure
        $update = new Update(
            sprintf('/review/%d', (int)$activity->getReview()?->getId()),
            json_encode($payload, JSON_THROW_ON_ERROR),
            true
        );
        $this->mercureHub->publish($update);
    }
}

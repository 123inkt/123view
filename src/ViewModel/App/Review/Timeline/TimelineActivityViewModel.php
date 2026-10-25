<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Review\Timeline;

use DR\Review\Entity\Review\CodeReviewActivity;
use Symfony\Component\Serializer\Attribute\Groups;

class TimelineActivityViewModel
{
    /**
     * @param array<string, int|float|bool|string|null> $data
     * @param array<string, int|string>|null             $user
     * @param array<string, int|string|array<string, int|string|bool>> $review
     */
    public function __construct(
        #[Groups('app:timeline')]
        public readonly int $id,
        #[Groups('app:timeline')]
        public readonly ?array $user,
        #[Groups('app:timeline')]
        public readonly array $review,
        #[Groups('app:timeline')]
        public readonly string $eventName,
        #[Groups('app:timeline')]
        public readonly array $data,
        #[Groups('app:timeline')]
        public readonly int $createTimestamp
    ) {
    }

    public static function fromActivity(CodeReviewActivity $activity): self
    {
        $user       = $activity->getUser();
        $review     = $activity->getReview();
        $repository = $review->getRepository();

        return new self(
            $activity->getId(),
            $user === null ? null : [
                'id'    => $user->getId(),
                'name'  => $user->getName(),
                'email' => $user->getEmail(),
            ],
            [
                'id'         => $review->getId(),
                'projectId'  => $review->getProjectId(),
                'title'      => $review->getTitle(),
                'description' => $review->getDescription(),
                'type'        => $review->getType(),
                'repository'  => [
                    'id'             => $repository->getId(),
                    'name'           => $repository->getName(),
                    'active'         => $repository->isActive(),
                    'displayName'    => $repository->getDisplayName(),
                    'mainBranchName' => $repository->getMainBranchName(),
                ],
            ],
            $activity->getEventName(),
            $activity->getData(),
            $activity->getCreateTimestamp()
        );
    }
}

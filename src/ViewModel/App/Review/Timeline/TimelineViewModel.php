<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Review\Timeline;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Review\ViewModelProvider\ReviewTimelineViewModelProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[Get(
    '/view-model/projects/timeline',
    openapi             : new OpenApiOperation(tags: ['ViewModel']),
    normalizationContext: [
        'groups' => [
            'app:timeline',
            'review-activity:read',
            'user:read',
            'code-review:read',
            'repository:read',
            'comment:read',
            'comment-reply:read'
        ]
    ],
    security            : 'is_granted("ROLE_USER")',
    provider            : ReviewTimelineViewModelProvider::class
)]
class TimelineViewModel
{
    /**
     * @codeCoverageIgnore
     *
     * @param CodeReviewActivity[]     $activities
     * @param TimelineEntryViewModel[] $entries
     */
    public function __construct(
        #[Groups('app:timeline')]
        public array $activities,
        public array $entries
    ) {
    }
}

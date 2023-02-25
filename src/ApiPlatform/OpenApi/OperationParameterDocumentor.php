<?php
declare(strict_types=1);

namespace DR\Review\ApiPlatform\OpenApi;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;

class OperationParameterDocumentor
{
    private const DESCRIPTIONS = [
        'api_code-review-activities_get_collection' => [
            'id'                       => 'Exact search for the code review activity id',
            'user.id'                  => 'Exact search for the user id of the activity',
            'review.id'                => 'Exact search for the review id of the activity',
            'review.title'             => 'Partial search for the review title of the activity',
            'review.state'             => 'Exact search for the review state. `open`|`closed`',
            'eventName'                =>
                "Exact search for the activity event name. Events:`\n- `comment-added`\n- `comment-removed`\n- `comment-reply-added`" .
                "\n- `comment-reply-updated`\n- `comment-resolved`\n- `comment-unresolved`\n- `comment-updated`\n- `review-accepted`\n" .
                "- `review-closed`\n- `review-created`\n- `review-opened`\n- `review-rejected`\n- `review-resumed`\n- `review-revision-added`" .
                "\n- `review-revision-removed`\n- `reviewer-added`\n- `reviewer-removed`\n- `reviewer-state-changed`",
            'createTimestamp[between]' => 'Search between two timestamp values. Format: `<start>..<end>`',
            'createTimestamp[gt]'      => 'Search for createTimestamp greater than the value',
            'createTimestamp[gte]'     => 'Search for createTimestamp greater or equal than the value',
            'createTimestamp[lt]'      => 'Search for createTimestamp lesser than the value',
            'createTimestamp[lte]'     => 'Search for createTimestamp lesser or equal than the value',
        ],
        'api_code-reviews_get_collection'           => [
            'title'                    => 'Partial search for the review title',
            'repository.id'            => 'Exact search for the repository id of the review',
            'state'                    => 'Exact search for the review state. `open`|`closed`',
            'createTimestamp[between]' => 'Search between two timestamp values. Format: `<start>..<end>`',
            'createTimestamp[gt]'      => 'Search for createTimestamp greater than the value',
            'createTimestamp[gte]'     => 'Search for createTimestamp greater or equal than the value',
            'createTimestamp[lt]'      => 'Search for createTimestamp lesser than the value',
            'createTimestamp[lte]'     => 'Search for createTimestamp lesser or equal than the value',
            'updateTimestamp[between]' => 'Search between two timestamp values. Format: `<start>..<end>`',
            'updateTimestamp[gt]'      => 'Search for updateTimestamp greater than the value',
            'updateTimestamp[gte]'     => 'Search for updateTimestamp greater or equal than the value',
            'updateTimestamp[lt]'      => 'Search for updateTimestamp lesser than the value',
            'updateTimestamp[lte]'     => 'Search for updateTimestamp lesser or equal than the value',
        ]
    ];

    public function getDescription(Operation $operation, Parameter $parameter): string
    {
        return self::DESCRIPTIONS[$operation->getOperationId()][$parameter->getName()] ?? '';
    }
}

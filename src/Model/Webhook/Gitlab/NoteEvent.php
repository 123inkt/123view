<?php
declare(strict_types=1);

namespace DR\Review\Model\Webhook\Gitlab;

use DR\Review\Model\Api\Gitlab\MergeRequest;
use DR\Review\Model\Api\Gitlab\Position;
use DR\Review\Model\Api\Gitlab\User;
use Stringable;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Attribute\SerializedPath;

class NoteEvent implements Stringable
{
    #[SerializedPath('[object_attributes][id]')]
    public int $id;

    #[SerializedName('project_id')]
    public int $projectId;

    #[SerializedPath('[object_attributes][discussion_id]')]
    public string $discussionId;

    #[SerializedPath('[object_attributes][note]')]
    public string $note;

    /** @phpstan-var 'Commit'|'MergeRequest'|'Issue'|'Snippet' */
    #[SerializedPath('[object_attributes][noteable_type]')]
    public string $noteType;

    /** @phpstan-var 'create'|'update' */
    #[SerializedPath('[object_attributes][action]')]
    public string $action;

    #[SerializedPath('[merge_request]')]
    public ?MergeRequest $mergeRequest;

    #[SerializedPath('[object_attributes][position]')]
    public ?Position $position = null;

    public User $user;

    public function __toString(): string
    {
        return sprintf(
            'NoteEvent(id: %s, mergeRequestIID %s, type: %s, action: %s)',
            $this->id,
            isset($this->mergeRequest) ? $this->mergeRequest->mergeRequestIId : '-',
            $this->noteType,
            $this->action
        );
    }
}

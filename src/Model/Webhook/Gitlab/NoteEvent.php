<?php
declare(strict_types=1);

namespace DR\Review\Model\Webhook\Gitlab;

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

    #[SerializedPath('[merge_request][iid]')]
    public int $mergeRequestIId;

    #[SerializedPath('[merge_request][source_branch]')]
    public string $sourceBranch;

    #[SerializedPath('[merge_request][target_branch]')]
    public string $targetBranch;

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

    #[SerializedPath('[object_attributes][position]')]
    public Position $position;

    public User $user;

    public function __toString(): string
    {
        return sprintf(
            'NoteEvent(id: %s, mergeRequestIID %s, type: %s, action: %s)',
            $this->id,
            $this->mergeRequestIId,
            $this->noteType,
            $this->action
        );
    }
}

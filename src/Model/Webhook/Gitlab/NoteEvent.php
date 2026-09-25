<?php
declare(strict_types=1);

namespace DR\Review\Model\Webhook\Gitlab;

use DR\Review\Model\Api\Gitlab\Position;
use DR\Review\Model\Api\Gitlab\User;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Attribute\SerializedPath;

class NoteEvent
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

    #[SerializedPath('[object_attributes][description]')]
    public string $description;

    #[SerializedPath('[object_attributes][action]')]
    public string $action;

    #[SerializedPath('[object_attributes][position]')]
    public Position $position;

    public User $user;
}

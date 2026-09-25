<?php
declare(strict_types=1);

namespace DR\Review\Model\Webhook\Gitlab;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Attribute\SerializedPath;

class NoteEvent
{
    #[SerializedPath('[object_attributes][id]')]
    public int $id;

    #[SerializedName('project_id')]
    public int $projectId;

    #[SerializedPath('[merge_request][id]')]
    public int $mergeRequestId;

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

    #[SerializedPath('[object_attributes][position][old_path]')]
    public ?string $oldPath;

    #[SerializedPath('[object_attributes][position][new_path]')]
    public ?string $newPath;

    #[SerializedPath('[object_attributes][position][old_line]')]
    public ?int $oldLine;

    #[SerializedPath('[object_attributes][position][new_line]')]
    public ?int $newLine;

    #[SerializedPath('[object_attributes][position][head_sha]')]
    public string $headSha;

    #[SerializedPath('[user][id]')]
    public int $userId;

    #[SerializedPath('[user][email]')]
    public string $userEmail;
}

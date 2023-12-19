<?php
declare(strict_types=1);

namespace DR\Review\Model\Api\Gitlab;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Serializer\Annotation\SerializedPath;

class NoteEvent
{
    #[SerializedName('project_id')]
    public int $projectId;

    #[SerializedPath('[merge_request][id]')]
    public int $mergeRequestId;

    #[SerializedPath('[merge_request][iid]')]
    public int $mergeRequestIId;

    #[SerializedPath('[object_attributes][discussion_id]')]
    public string $discussionId;

    #[SerializedPath('[object_attributes][description]')]
    public string $description;

    #[SerializedPath('[user][id]')]
    public int $userId;

    #[SerializedPath('[user][email]')]
    public string $userEmail;
}

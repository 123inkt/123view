<?php
declare(strict_types=1);

namespace DR\Review\Model\Webhook\Gitlab;

use DR\Review\Model\Api\Gitlab\Project;
use DR\Review\Model\Api\Gitlab\User;
use Symfony\Component\Serializer\Attribute\SerializedPath;

class MergeRequestEvent
{
    public User    $user;
    public Project $project;
    #[SerializedPath('[object_attributes][iid]')]
    public int     $iid;
    #[SerializedPath('[object_attributes][action]')]
    public string  $action;
    #[SerializedPath('[object_attributes][source_branch]')]
    public string  $sourceBranch;
}

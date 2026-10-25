<?php
declare(strict_types=1);

namespace DR\Review\Model\Webhook\Gitlab;

use DR\Review\Model\Api\Gitlab\Project;
use DR\Review\Model\Api\Gitlab\User;
use Stringable;
use Symfony\Component\Serializer\Attribute\SerializedPath;

class MergeRequestEvent implements Stringable
{
    public User    $user;
    public Project $project;
    #[SerializedPath('[object_attributes][iid]')]
    public int     $iid;
    #[SerializedPath('[object_attributes][action]')]
    public string  $action;
    #[SerializedPath('[object_attributes][source_branch]')]
    public string  $sourceBranch;

    public function __toString(): string
    {
        return sprintf('MergeRequestEvent(project: %s, action: %s, sourceBranch: %s)', $this->project->name, $this->action, $this->sourceBranch);
    }
}

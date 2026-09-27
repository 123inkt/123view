<?php
declare(strict_types=1);

namespace DR\Review\Model\Api\Gitlab;

use Symfony\Component\Serializer\Attribute\SerializedName;

class MergeRequest
{
    #[SerializedName('iid')]
    public int $mergeRequestIId;

    #[SerializedName('source_branch')]
    public string $sourceBranch;

    #[SerializedName('target_branch')]
    public string $targetBranch;
}

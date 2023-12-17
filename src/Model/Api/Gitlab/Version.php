<?php

declare(strict_types=1);

namespace DR\Review\Model\Api\Gitlab;

use Symfony\Component\Serializer\Attribute\SerializedName;

class Version
{
    public int     $id;
    #[SerializedName('head_commit_sha')]
    public string  $headCommitSha;
    #[SerializedName('base_commit_sha')]
    public string  $baseCommitSha;
    #[SerializedName('start_commit_sha')]
    public string  $startCommitSha;
    #[SerializedName('created_at')]
    public string  $createdAt;
    #[SerializedName('merge_request_id')]
    public int     $mergeRequestId;
    public string  $state;
    #[SerializedName('real_size')]
    public string  $realSize;
    #[SerializedName('patch_id_sha')]
    public ?string $patchIdSha;
}

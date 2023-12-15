<?php

declare(strict_types=1);

namespace DR\Review\Model\Api\Gitlab;

class Version
{
    public int     $id;
    public string  $headCommitSha;
    public string  $baseCommitSha;
    public string  $startCommitSha;
    public string  $createdAt;
    public int     $mergeRequestId;
    public string  $state;
    public string  $realSize;
    public ?string $patchIdSha;
}

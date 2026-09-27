<?php
declare(strict_types=1);

namespace DR\Review\Model\Api\Gitlab;

use Symfony\Component\Serializer\Attribute\SerializedName;

class Position
{
    #[SerializedName('position_type')]
    public string  $positionType;
    #[SerializedName('base_sha')]
    public string  $baseSha;
    #[SerializedName('head_sha')]
    public string  $headSha;
    #[SerializedName('start_sha')]
    public string  $startSha;
    #[SerializedName('old_path')]
    public ?string $oldPath = null;
    #[SerializedName('new_path')]
    public ?string $newPath = null;
    #[SerializedName('old_line')]
    public ?int    $oldLine = null;
    #[SerializedName('new_line')]
    public ?int    $newLine = null;
}

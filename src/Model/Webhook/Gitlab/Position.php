<?php
declare(strict_types=1);

namespace DR\Review\Model\Webhook\Gitlab;

use Symfony\Component\Serializer\Annotation\SerializedName;

class Position
{
    #[SerializedName('base_sha')]
    public string  $baseSha;
    #[SerializedName('start_sha')]
    public string  $startSha;
    #[SerializedName('head_sha')]
    public string  $headSha;
    #[SerializedName('old_path')]
    public ?string $oldPath;
    #[SerializedName('new_path')]
    public ?string $newPath;
    #[SerializedName('old_line')]
    public ?int    $oldLine;
    #[SerializedName('new_line')]
    public ?int    $newLine;
}

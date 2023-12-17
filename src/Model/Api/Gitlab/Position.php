<?php
declare(strict_types=1);

namespace DR\Review\Model\Api\Gitlab;

class Position
{
    public string  $positionType;
    public string  $baseSha;
    public string  $headSha;
    public string  $startSha;
    public ?string $oldPath = null;
    public ?string $newPath = null;
    public ?int    $oldLine = null;
    public ?int    $newLine = null;
}

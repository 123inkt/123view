<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Git\Diff;

class DiffChange
{
    public const ADDED     = 1;
    public const REMOVED   = 2;
    public const NEWLINE   = 3;
    public const UNCHANGED = 4;

    public int    $type;
    public string $code;

    public function __construct(int $type, string $code)
    {
        $this->type = $type;
        $this->code = $code;
    }
}

<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Diff\Bundle;

class DiffLineCompareResult
{
    public int $removals;
    public int $additions;
    public int $whitespace;

    public function __construct(int $removals, int $additions, int $whitespace)
    {
        $this->removals   = $removals;
        $this->additions  = $additions;
        $this->whitespace = $whitespace;
    }

    public function isWhitespaceOnly(): bool
    {
        return $this->removals === 0 && $this->additions === 0 && $this->whitespace > 0;
    }

    public function isRemovalOnly(): bool
    {
        return $this->removals > 0 && $this->additions === 0;
    }

    public function isAdditionsOnly(): bool
    {
        return $this->removals === 0 && $this->additions > 0;
    }
}

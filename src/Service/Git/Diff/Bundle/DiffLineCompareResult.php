<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Diff\Bundle;

class DiffLineCompareResult
{
    public function __construct(
        public readonly int $removals,
        public readonly int $additions,
        public readonly int $whitespace,
        public readonly int $levenshtein
    ) {
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

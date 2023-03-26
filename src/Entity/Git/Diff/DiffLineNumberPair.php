<?php
declare(strict_types=1);

namespace DR\Review\Entity\Git\Diff;

class DiffLineNumberPair
{
    public function __construct(private int $lineNumberBefore, private int $lineNumberAfter)
    {
    }

    public function increment(bool $after, int $amount = 1): void
    {
        if ($after) {
            $this->lineNumberAfter += $amount;
        } else {
            $this->lineNumberBefore += $amount;
        }
    }

    public function getLineNumberBefore(): int
    {
        return $this->lineNumberBefore;
    }

    public function getLineNumberAfter(): int
    {
        return $this->lineNumberAfter;
    }
}

<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Review;

use InvalidArgumentException;

class LineReference
{
    public readonly int $lineBefore;
    public readonly int $lineAfter;

    public function __construct(string $lineReference)
    {
        if (preg_match('/^(\d*):(\d*)$/', $lineReference, $matches) !== 1) {
            throw new InvalidArgumentException('Invalid line reference: ' . $lineReference);
        }

        $this->lineBefore = (int)$matches[1];
        $this->lineAfter  = (int)$matches[2];
    }

    public function getLine(): int
    {
        return $this->lineAfter === 0 ? $this->lineBefore : $this->lineAfter;
    }

    public function __toString(): string
    {
        return sprintf('%d:%d', $this->lineBefore, $this->lineAfter);
    }
}

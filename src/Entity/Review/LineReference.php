<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Review;

use InvalidArgumentException;

class LineReference
{
    public function __construct(public readonly string $filePath, public readonly int $line, public readonly int $offset)
    {
    }

    public static function fromString(string $reference): LineReference
    {
        if (preg_match('/(.*):(\d+):(\d+)/', $reference, $matches) !== 1) {
            throw new InvalidArgumentException('Invalid reference: ' . $reference);
        }

        return new LineReference($matches[1], (int)$matches[2], (int)$matches[3]);
    }

    public function __toString(): string
    {
        return sprintf('%s:%d:%d', $this->filePath, $this->line, $this->offset);
    }
}

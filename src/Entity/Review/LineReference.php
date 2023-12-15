<?php
declare(strict_types=1);

namespace DR\Review\Entity\Review;

use InvalidArgumentException;
use Stringable;

class LineReference implements Stringable
{
    public function __construct(
        public readonly ?string $oldFilePath = null,
        public readonly string $filePath = '',
        public readonly int $line = 1,
        public readonly int $offset = 0,
        public readonly int $lineAfter = 1,
        public readonly LineReferenceStateEnum $state = LineReferenceStateEnum::Unknown
    ) {
    }

    public static function fromString(string $reference): LineReference
    {
        if (preg_match('/^(.*?):(.*?):(\d+):(\d+):(\d+):(.)$/', $reference, $matches) === 1) {
            $oldFilePath = $matches[1] === 'null' ? null : $matches[1];
            $state       = LineReferenceStateEnum::from($matches[6]);

            return new LineReference($oldFilePath, $matches[2], (int)$matches[3], (int)$matches[4], (int)$matches[5], $state);
        }

        if (preg_match('/^(.*):(\d+):(\d+):(\d+)$/', $reference, $matches) === 1) {
            // backwards compat line reference (file:line:offset:lineAfter)
            $line      = (int)$matches[2];
            $offset    = (int)$matches[3];
            $lineAfter = (int)$matches[4];
            $state     = LineReferenceStateEnum::Unknown;

            if ($offset > 0) {
                $state = LineReferenceStateEnum::Added;
            } elseif ($lineAfter === 0) {
                $state = LineReferenceStateEnum::Deleted;
            }

            return new LineReference(null, $matches[1], $line, $offset, $lineAfter, $state);
        }

        throw new InvalidArgumentException('Invalid reference: ' . $reference);
    }

    public function __toString(): string
    {
        return sprintf('%s:%s:%d:%d:%d:%s', $this->oldFilePath, $this->filePath, $this->line, $this->offset, $this->lineAfter, $this->state->value);
    }
}

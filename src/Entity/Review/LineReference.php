<?php
declare(strict_types=1);

namespace DR\Review\Entity\Review;

use InvalidArgumentException;
use Stringable;

class LineReference implements Stringable
{
    public function __construct(
        public readonly ?string $oldPath = null,
        public readonly ?string $newPath = null,
        public readonly int $line = 1,
        public readonly int $offset = 0,
        public readonly int $lineAfter = 1,
        public readonly ?string $headSha = null,
        public readonly LineReferenceStateEnum $state = LineReferenceStateEnum::Unknown
    ) {
    }

    public static function fromString(string $reference): LineReference
    {
        if (preg_match('/^(.*?):(.*?):(\d+):(\d+):(\d+):(\w*):(.)$/', $reference, $matches) === 1) {
            $oldPath = $matches[1] === '' ? null : $matches[1];
            $newPath = $matches[2] === '' ? null : $matches[2];
            $headSha = $matches[6] === '' ? null : $matches[6];
            $state   = LineReferenceStateEnum::from($matches[7]);

            return new LineReference($oldPath, $newPath, (int)$matches[3], (int)$matches[4], (int)$matches[5], $headSha, $state);
        }

        if (preg_match('/^(.*):(\d+):(\d+):(\d+)$/', $reference, $matches) === 1) {
            // backwards compat line reference (file:line:offset:lineAfter)
            return new LineReference(null, $matches[1], (int)$matches[2], (int)$matches[3], (int)$matches[4], null, LineReferenceStateEnum::Unknown);
        }

        throw new InvalidArgumentException('Invalid reference: ' . $reference);
    }

    public function __toString(): string
    {
        return sprintf(
            '%s:%s:%d:%d:%d:%s:%s',
            $this->oldPath,
            $this->newPath,
            $this->line,
            $this->offset,
            $this->lineAfter,
            $this->headSha,
            $this->state->value
        );
    }
}

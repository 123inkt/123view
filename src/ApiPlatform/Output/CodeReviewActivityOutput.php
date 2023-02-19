<?php
declare(strict_types=1);

namespace DR\Review\ApiPlatform\Output;

class CodeReviewActivityOutput
{
    /**
     * @codeCoverageIgnore
     *
     * @param array<string, int|float|bool|string|null> $data
     */
    public function __construct(
        public readonly int $id,
        public readonly int $userId,
        public readonly int $reviewId,
        public readonly string $eventName,
        public readonly array $data,
        public readonly int $createTimestamp
    ) {
    }
}

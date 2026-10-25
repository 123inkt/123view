<?php

declare(strict_types=1);

namespace DR\Review\ApiPlatform\Output;

use DateTimeImmutable;

class CommentReplyOutput
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(
        public readonly int $id,
        public readonly int $commentId,
        public readonly int $userId,
        public readonly string $message,
        public readonly ?string $tag,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
    ) {
    }
}

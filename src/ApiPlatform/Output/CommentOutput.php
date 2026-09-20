<?php

declare(strict_types=1);

namespace DR\Review\ApiPlatform\Output;

class CommentOutput
{
    /**
     * @codeCoverageIgnore
     * @SuppressWarnings(ExcessiveParameterList)
     */
    public function __construct(
        public readonly int $id,
        public readonly int $userId,
        public readonly int $reviewId,
        public readonly string $message,
        public readonly string $filepath,
        public readonly int $line,
        public readonly ?string $sha,
        public readonly string $state,
        public readonly ?string $tag,
        public readonly int $createTimestamp,
        public readonly int $updateTimestamp,
    ) {
    }
}

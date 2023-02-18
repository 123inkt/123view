<?php
declare(strict_types=1);

namespace DR\Review\ApiPlatform\Output;

class CodeReviewOutput
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(
        public readonly int $id,
        public readonly int $repositoryId,
        public readonly string $slug,
        public readonly string $title,
        public readonly string $description,
        public readonly string $url,
        public readonly string $state,
        public readonly string $reviewerState,
        public readonly int $createTimestamp,
        public readonly int $updateTimestamp,
    ) {
    }
}

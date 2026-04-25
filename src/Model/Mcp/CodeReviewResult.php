<?php

declare(strict_types=1);

namespace DR\Review\Model\Mcp;

readonly class CodeReviewResult
{
    /**
     * @codeCoverageIgnore Simple DTO
     */
    public function __construct(
        public int $id,
        public string $title,
        public ?string $state,
        public string $reviewerState,
        public string $repository,
        public string $url,
    ) {
    }
}

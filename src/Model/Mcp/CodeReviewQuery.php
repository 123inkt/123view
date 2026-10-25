<?php

declare(strict_types=1);

namespace DR\Review\Model\Mcp;

readonly class CodeReviewQuery
{
    /**
     * @codeCoverageIgnore Simple DTO
     */
    public function __construct(
        public ?string $title = null,
        public ?string $branchName = null,
        public ?string $authorEmail = null,
        public ?string $repositoryUrl = null,
        public ?string $state = null,
    ) {
    }
}

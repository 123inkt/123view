<?php
declare(strict_types=1);

namespace DR\Review\Model\Api\Anthropic;

readonly class CodeReviewResponse
{
    public function __construct(
        public string $filepath,
        public int $lineNumber,
        public string $message
    ) {
    }
}

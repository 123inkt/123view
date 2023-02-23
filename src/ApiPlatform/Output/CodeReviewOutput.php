<?php
declare(strict_types=1);

namespace DR\Review\ApiPlatform\Output;

class CodeReviewOutput
{
    /**
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     *
     * @param UserOutput[] $authors
     * @param UserOutput[] $reviewers
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
        public readonly ?array $authors,
        public readonly ?array $reviewers,
        public readonly int $createTimestamp,
        public readonly int $updateTimestamp,
    ) {
    }
}

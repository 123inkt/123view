<?php
declare(strict_types=1);

namespace DR\Review\Entity\Git\Fetch;

class BranchUpdate
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(
        public readonly string $fromHash,
        public readonly string $toHash,
        public readonly string $localBranch,
        public readonly string $remoteBranch,
    ) {
    }
}

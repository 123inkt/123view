<?php
declare(strict_types=1);

namespace DR\Review\Entity\Git\Fetch;

class BranchCreation
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(public readonly string $localBranch, public readonly string $remoteBranch)
    {
    }
}

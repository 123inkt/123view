<?php
declare(strict_types=1);

namespace DR\Review\Entity\Git\CherryPick;

class CherryPickResult
{
    /**
     * @codeCoverageIgnore
     *
     * @param string[] $conflicts
     */
    public function __construct(public readonly bool $completed, public readonly array $conflicts = [])
    {
    }
}

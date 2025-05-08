<?php
declare(strict_types=1);

namespace DR\Review\Model\Search\RipGrep;

class RipGrepResult
{
    public function __construct(public readonly string $output, public readonly int $exitCode)
    {
    }
}

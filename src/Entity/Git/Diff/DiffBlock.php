<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Git\Diff;

class DiffBlock
{
    /** @var DiffLine[] */
    public array $lines = [];
}

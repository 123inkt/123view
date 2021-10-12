<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Git\Diff;

class DiffLinePair
{
    public DiffLine $removed;
    public DiffLine $added;

    public function __construct(DiffLine $removed, DiffLine $added)
    {
        $this->removed = $removed;
        $this->added   = $added;
    }
}

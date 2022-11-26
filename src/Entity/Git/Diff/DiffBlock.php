<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Git\Diff;

class DiffBlock
{
    /** @var DiffLine[] */
    public array $lines = [];

    public function isLineVisible(int $index, int $margin): bool
    {
        $max = $index + $margin;

        // check lines and sibling for changes
        for ($i = $index - $margin; $i <= $max; $i++) {
            if (isset($this->lines[$i]) && $this->lines[$i]->state !== DiffLine::STATE_UNCHANGED) {
                return true;
            }
        }

        return false;
    }
}

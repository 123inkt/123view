<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\CherryPick;

use DR\Review\Entity\Git\CherryPick\CherryPickResult;

class GitCherryPickParser
{
    public function parse(string $output): CherryPickResult
    {
        if (str_contains($output, "no cherry-pick or revert in progress")) {
            return new CherryPickResult(true);
        }

        $result = preg_match_all('/CONFLICT\s+\(\S+\):\s+(\S+)/', $output, $matches);
        if ($result === false || $result === 0) {
            // no conflicts found
            return new CherryPickResult(false);
        }

        return new CherryPickResult(false, $matches[1]);
    }
}

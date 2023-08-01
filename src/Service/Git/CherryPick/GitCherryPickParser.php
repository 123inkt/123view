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

        if (preg_match_all('/CONFLICT\s+\(\S+\):\s+\S+\s+renamed to\s+(\S+)/', $output, $matches) > 0) {
            return new CherryPickResult(false, $matches[1]);
        }

        if (preg_match_all('/CONFLICT\s+\(\S+\):\s+(\S+)/', $output, $matches) > 0) {
            return new CherryPickResult(false, $matches[1]);
        }

        return new CherryPickResult(false);
    }
}

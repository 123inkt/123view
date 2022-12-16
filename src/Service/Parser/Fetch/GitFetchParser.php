<?php
declare(strict_types=1);

namespace DR\Review\Service\Parser\Fetch;

use DR\Review\Entity\Git\Fetch\BranchCreation;
use DR\Review\Entity\Git\Fetch\BranchUpdate;

class GitFetchParser
{
    /**
     * @return array<BranchCreation|BranchUpdate>
     */
    public function parse(string $fetchLog): array
    {
        $result = [];

        // * [new branch]            NewBranch              -> origin/NewBranch
        $count = preg_match_all('/^\s*[+*=]?\s*\[new branch]\s+(\S+)\s+->\s+(\S+)\s*/m', $fetchLog, $matches);
        for ($i = 0; $i < $count; $i++) {
            $result[] = new BranchCreation($matches[1][$i], $matches[2][$i]);
        }

        //    0058886bd1..f56b867839  local_branch_name -> origin/remote_branch_name
        $count = preg_match_all('/^\s*[+*=]?\s*(\w+)\.{2,3}(\w+)\s+(\S+)\s+->\s+(\S+)\s*/m', $fetchLog, $matches);
        for ($i = 0; $i < $count; $i++) {
            $result[] = new BranchUpdate($matches[1][$i], $matches[2][$i], $matches[3][$i], $matches[4][$i]);
        }

        return $result;
    }
}

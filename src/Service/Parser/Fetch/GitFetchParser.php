<?php
declare(strict_types=1);

namespace DR\Review\Service\Parser\Fetch;

use DR\Review\Entity\Git\Fetch\BranchUpdate;

class GitFetchParser
{
    /**
     * @return BranchUpdate[]
     */
    public function parse(string $fetchLog): array
    {
        //    0058886bd1..f56b867839  local_branch_name -> origin/remove_branch_name
        $count = preg_match_all('/^\s*(\w+)\.{2,3}(\w+)\s+(\S+)\s+->\s+(\S+)$/m', $fetchLog, $matches);

        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = new BranchUpdate($matches[1][$i], $matches[2][$i], $matches[3][$i], $matches[4][$i]);
        }

        return $result;
    }
}

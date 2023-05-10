<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Branch;

class GitRemoteBranchParser
{
    /**
     * @return string[]
     */
    public function parse(string $output): array
    {
        $branches = [];

        foreach (explode("\n", $output) as $line) {
            if (preg_match('/^\s*(\S+)/', $line, $matches) === 1) {
                $branches[] = $matches[1];
            }
        }

        return $branches;
    }
}

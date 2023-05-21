<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\RevList;

class GitRevListParser
{
    /**
     * @return string[]
     */
    public function parseOneLine(string $data): array
    {
        $count = preg_match_all('/^>([a-z0-9]+)/im', $data, $matches);
        if ($count === 0 || $count === false || isset($matches[1]) === false) {
            return [];
        }

        return $matches[1];
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Service\Search\RipGrep;

use DR\Review\Service\Search\RipGrep\Iterator\JsonDecodeIterator;

/**
 * @phpstan-import-type SearchResultEntry from JsonDecodeIterator
 */
class SearchResultLineMatcher
{
    /**
     * @param SearchResultEntry $entry
     */
    public function matches(array $entry, ?string $filenamePattern): bool
    {
        if ($entry['type'] !== 'begin') {
            return false;
        }

        return $filenamePattern === null || @preg_match('~' . $filenamePattern . '~', $entry['data']['path']['text']) === 1;
    }
}

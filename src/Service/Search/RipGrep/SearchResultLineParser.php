<?php
declare(strict_types=1);

namespace DR\Review\Service\Search\RipGrep;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Model\Search\SearchResultCollection;
use DR\Review\Service\Search\RipGrep\Iterator\JsonDecodeIterator;

/**
 * @phpstan-import-type SearchResultEntry from JsonDecodeIterator
 */
readonly class SearchResultLineParser
{
    public function __construct(
        private string $gitCacheDirectory,
        private SearchResultFactory $resultFactory,
        private SearchResultLineFactory $resultLineFactory,
        private SearchResultLineMatcher $resultLineMatcher,
    ) {
    }

    /**
     * @param iterable<int, SearchResultEntry> $iterator
     * @param Repository[]                     $repositories
     */
    public function parse(iterable $iterator, array $repositories, ?int $limit = null, ?string $filenamePattern = null): SearchResultCollection
    {
        $results              = [];
        $current              = null;
        $moreResultsAvailable = false;
        foreach ($iterator as $entry) {
            if ($this->resultLineMatcher->matches($entry, $filenamePattern)) {
                $current = $this->resultFactory->create($entry['data']['path']['text'], $this->gitCacheDirectory, $repositories);
                continue;
            }

            if ($current === null) {
                continue;
            }

            if ($entry['type'] === 'end') {
                $results[] = $current;
                $current   = null;
            } elseif ($entry['type'] === 'context') {
                $current->addLine($this->resultLineFactory->createContextFromEntry($entry));
            } elseif ($entry['type'] === 'match') {
                $current->addLine($this->resultLineFactory->createMatchFromEntry($entry));
            }

            if ($limit !== null && count($results) >= $limit) {
                $moreResultsAvailable = true;
                break;
            }
        }

        return new SearchResultCollection($results, $moreResultsAvailable);
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Service\Search\RipGrep;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Model\Search\SearchResult;
use DR\Review\Service\Search\RipGrep\Iterator\JsonDecodeIterator;

/**
 * @phpstan-import-type SearchResultEntry from JsonDecodeIterator
 */
class SearchResultLineParser
{
    public function __construct(
        private readonly string $gitCacheDirectory,
        private readonly SearchResultFactory $resultFactory,
        private readonly SearchResultLineFactory $resultLineFactory
    ) {
    }

    /**
     * @param iterable<int, SearchResultEntry> $iterator
     * @param Repository[]                     $repositories
     *
     * @return SearchResult[]
     */
    public function parse(iterable $iterator, array $repositories): array
    {
        $results = [];
        $current = null;
        foreach ($iterator as $entry) {
            if ($entry['type'] === 'begin') {
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

            // @codeCoverageIgnoreStart
            if (count($results) >= 100) {
                break;
            }
            // @codeCoverageIgnoreEnd
        }

        return $results;
    }
}

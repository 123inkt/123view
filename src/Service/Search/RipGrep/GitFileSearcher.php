<?php
declare(strict_types=1);

namespace DR\Review\Service\Search\RipGrep;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Model\Search\SearchResult;
use DR\Review\Service\Search\RipGrep\Iterator\JsonDecodeIterator;

class GitFileSearcher
{
    private const DEFAULT_ARGUMENTS = [
        '--hidden',
        '--color=never',
        '--line-number',
        '--after-context=5',
        '--before-context=5',
        '--glob=!.git/',
        '--json'
    ];

    public function __construct(
        private readonly string $gitCacheDirectory,
        private readonly RipGrepProcessExecutor $executor,
        private readonly SearchResultFactory $resultFactory,
        private readonly SearchResultLineFactory $resultLineFactory
    ) {
    }

    /**
     * @param Repository[] $repositories
     *
     * @return SearchResult[]
     */
    public function find(string $searchQuery, array $repositories): array
    {
        $arguments = self::DEFAULT_ARGUMENTS;
        array_push($arguments, $searchQuery);

        $jsonIterator = new JsonDecodeIterator($this->executor->execute($arguments, $this->gitCacheDirectory));

        $results = [];
        $current = null;
        foreach ($jsonIterator as $entry) {
            if ($entry['type'] === 'begin') {
                $current = $this->resultFactory->create($entry['data']['path']['text'], $this->gitCacheDirectory, $repositories);
            } elseif ($entry['type'] === 'end' && $current !== null) {
                $results[] = $current;
                $current   = null;
            } elseif ($entry['type'] === 'context' && $current !== null) {
                $current->addLine($this->resultLineFactory->createContextFromEntry($entry));
            } elseif ($entry['type'] === 'match' && $current !== null) {
                $current->addLine($this->resultLineFactory->createMatchFromEntry($entry));
            }

            if (count($results) >= 100) {
                break;
            }
        }

        return $results;
    }
}

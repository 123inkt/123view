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
        private readonly SearchResultLineParser $parser,
    ) {
    }

    /**
     * @param Repository[] $repositories
     *
     * @return SearchResult[]
     */
    public function find(string $searchQuery, ?string $extension, array $repositories): array
    {
        $arguments = self::DEFAULT_ARGUMENTS;
        array_push($arguments, $searchQuery);

        $jsonIterator = new JsonDecodeIterator($this->executor->execute($arguments, $this->gitCacheDirectory));

        return $this->parser->parse($jsonIterator, $repositories);
    }
}

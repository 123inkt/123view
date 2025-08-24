<?php
declare(strict_types=1);

namespace DR\Review\Service\Search\RipGrep;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Model\Search\SearchResult;
use DR\Review\Service\Search\RipGrep\Command\RipGrepCommandBuilderFactory;
use DR\Review\Service\Search\RipGrep\Command\RipGrepProcessExecutor;
use DR\Review\Service\Search\RipGrep\Iterator\JsonDecodeIterator;

class GitFileSearcher
{
    public function __construct(
        private readonly string $gitCacheDirectory,
        private readonly RipGrepCommandBuilderFactory $commandBuilderFactory,
        private readonly RipGrepProcessExecutor $executor,
        private readonly SearchResultLineParser $parser,
    ) {
    }

    /**
     * @param non-empty-array<string> $extensions
     * @param Repository[]            $repositories
     *
     * @return SearchResult[]
     */
    public function find(string $searchQuery, ?array $extensions, array $repositories, ?int $limit = null): array
    {
        $command = $this->commandBuilderFactory->default();
        $command->search($searchQuery);
        if ($extensions !== null) {
            $command->glob('*.{' . implode(',', $extensions) . '}');
        }

        $jsonIterator = new JsonDecodeIterator($this->executor->execute($command, $this->gitCacheDirectory));

        return $this->parser->parse($jsonIterator, $repositories, $limit);
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Service\Search\RipGrep;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Model\Search\SearchFilter;
use DR\Review\Model\Search\SearchResultCollection;
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
     * @param Repository[] $repositories
     */
    public function find(SearchFilter $filter, array $repositories, ?int $limit = null): SearchResultCollection
    {
        $command = $this->commandBuilderFactory->default();
        if ($filter->regexEnabled === false) {
            $command->fixedStrings();
        }

        $command->search($filter->searchQuery);

        $jsonIterator = new JsonDecodeIterator($this->executor->execute($command, $this->gitCacheDirectory));

        return $this->parser->parse($jsonIterator, $repositories, $limit, $filter->filename);
    }
}

<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Fetch;

use DR\Review\Entity\Git\Fetch\BranchCreation;
use DR\Review\Entity\Git\Fetch\BranchUpdate;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\GitRepositoryService;
use DR\Review\Service\Git\Log\GitLogService;
use DR\Review\Service\Parser\Fetch\GitFetchParser;
use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class GitFetchService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly GitCommandBuilderFactory $commandFactory,
        private readonly GitFetchParser $parser,
        private readonly GitLogService $logService,
        private readonly GitRepositoryService $gitRepositoryService,
    ) {
    }

    /**
     * @return array<BranchCreation|BranchUpdate>
     * @throws Exception
     */
    public function fetch(Repository $repository): array
    {
        $gitRepository = $this->gitRepositoryService->getRepository((string)$repository->getUrl());

        // fetch new revisions from remote
        $fetchCommand = $this->commandFactory->createFetch()->verbose()->all();
        $this->logger?->info(sprintf('Executing `%s` for `%s`', $fetchCommand, $repository->getName()));
        $output = $gitRepository->execute($fetchCommand, true);

        // parse branch updates
        $changes = $this->parser->parse($output);
        $this->logger?->info(sprintf('Fetch: %d new updates for `%s`', count($changes), $repository->getName()));

        return $changes;
    }
}

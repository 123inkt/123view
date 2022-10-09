<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Command\Review;

use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Repository\Config\RepositoryRepository;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use DR\GitCommitNotification\Service\Git\Log\GitLogCommandBuilderFactory;
use DR\GitCommitNotification\Service\Parser\GitLogParser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('review:import', 'Import reviews from a git repository')]
class ImportReviewCommand extends Command
{
    public function __construct(
        private RepositoryRepository $repositoryRepository,
        private CacheableGitRepositoryService $gitRepository,
        private GitLogCommandBuilderFactory $commandBuilderFactory,
        private GitLogParser $logParser,
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     * @throws RepositoryException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // get druid repository
        $repository = $this->repositoryRepository->findOneBy(['name' => 'Druid']);
        if ($repository === null) {
            return Command::FAILURE;
        }

        $command = $this->commandBuilderFactory->create();

        $result = $this->gitRepository->getRepository($repository->getUrl())->execute($command);


        return Command::SUCCESS;
    }
}

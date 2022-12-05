<?php
declare(strict_types=1);

namespace DR\Review\Command\Revision;

use DR\Review\Message\Revision\FetchRepositoryRevisionsMessage;
use DR\Review\Repository\Config\RepositoryRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand('revisions:fetch', "Fetch revisions for repositories at specific intervals")]
class FetchRevisionsCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly RepositoryRepository $repositoryRepository, private readonly MessageBusInterface $bus)
    {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->repositoryRepository->findByUpdateRevisions() as $repository) {
            $this->bus->dispatch(new FetchRepositoryRevisionsMessage((int)$repository->getId()));

            $repository->setUpdateRevisionsTimestamp(time());
            $this->repositoryRepository->save($repository, true);

            $this->logger?->info('FetchRevisions: for repository {repository}', ['repository' => $repository->getName()]);
        }

        return Command::SUCCESS;
    }
}

<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Command\Repository;

use DR\GitCommitNotification\Repository\Config\RepositoryRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('git:repository:list', 'List all existing repositories.')]
class ListRepositoriesCommand extends Command
{
    public function __construct(private RepositoryRepository $repositoryRepository, ?string $name = null)
    {
        parent::__construct($name);
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repositories = $this->repositoryRepository->findAll();
        if (count($repositories) === 0) {
            $output->writeln('<info>No existing repositories found.</info>');

            return Command::SUCCESS;
        }

        $output->writeln('Repositories: ');
        foreach ($repositories as $repository) {
            $output->writeln('- ' . $repository->getName());
        }

        return Command::SUCCESS;
    }
}

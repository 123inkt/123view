<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Command\Repository;

use DR\GitCommitNotification\Repository\Config\RepositoryRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('git:repository:remove', 'Remove a git repository from the database.')]
class RemoveRepositoryCommand extends Command
{
    public function __construct(private RepositoryRepository $repositoryRepository, ?string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'The name of the repository to remove');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repositoryName = $input->getArgument('name');
        $repository     = $this->repositoryRepository->findOneBy(['name' => $repositoryName]);
        if ($repository === null) {
            $output->writeln('<error>No repository found by the name: ' . $repositoryName);

            return Command::FAILURE;
        }

        $this->repositoryRepository->remove($repository, true);

        $output->writeln('<info>Successfully removed the repository: ' . $repository->getName() . '</info>');

        return Command::SUCCESS;
    }
}

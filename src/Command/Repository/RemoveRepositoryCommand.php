<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Command\Repository;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Entity\Repository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('git:repository:remove', 'Remove a git repository from the database.')]
class RemoveRepositoryCommand extends Command
{
    public function __construct(private ManagerRegistry $doctrine, ?string $name = null)
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
        $repository     = $this->doctrine->getRepository(Repository::class)->findOneBy(['name' => $repositoryName]);
        if ($repository === null) {
            $output->writeln('<error>No repository found by the name: ' . $repositoryName);

            return Command::FAILURE;
        }

        $this->doctrine->getManager()->remove($repository);
        $this->doctrine->getManager()->flush();

        $output->writeln('<info>Successfully removed the repository: ' . $repository->getName() . '</info>');

        return Command::SUCCESS;
    }
}

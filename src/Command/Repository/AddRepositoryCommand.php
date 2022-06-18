<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Command\Repository;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Entity\Repository;
use DR\GitCommitNotification\Entity\RepositoryProperty;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('git:repository:add', 'Add a git repository to monitor commits for.')]
class AddRepositoryCommand extends Command
{
    public function __construct(private ManagerRegistry $doctrine, ?string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addArgument('repository', InputArgument::REQUIRED, 'The url to the git repository');
        $this->addOption('name', '', InputOption::VALUE_REQUIRED, 'The name of the repository, if absent will the the basename of the url.');
        $this->addOption('upsource', '', InputOption::VALUE_REQUIRED, 'The upsource project id this repository is related to.');
        $this->addOption('gitlab', '', InputOption::VALUE_REQUIRED, 'The gitlab project id this repository is related to.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repository = new Repository();

        $repositoryUrl = (string)$input->getArgument('repository');
        $repository->setUrl($repositoryUrl);

        // determine name
        if ($input->getOption('name') === null) {
            if (preg_match('#/([^/]+?)(?:.git)?$#i', $repositoryUrl, $matches) !== 1) {
                $output->writeln(
                    '<error>Unable to determine the name of the repository based on the url. Specify repository name with the --name flag</error>'
                );

                return Command::FAILURE;
            }
            $repository->setName($matches[1]);
        } else {
            $repository->setName((string)$input->getOption('name'));
        }

        if ($input->getOption('upsource') !== null) {
            $property = new RepositoryProperty();
            $property->setName('upsource-project-id');
            $property->setValue((string)$input->getOption('upsource'));
            $repository->addRepositoryProperty($property);
        }

        if ($input->getOption('gitlab') !== null) {
            $property = new RepositoryProperty();
            $property->setName('gitlab-project-id');
            $property->setValue((string)$input->getOption('gitlab'));
            $repository->addRepositoryProperty($property);
        }

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($repository);
        $entityManager->flush();

        $output->writeln('<info>Successfully added repository: ' . $repository->getName() . '</info>');
        foreach ($repository->getRepositoryProperties() as $property) {
            $output->writeln(sprintf('<info>- property: %s: %s</info>', $property->getName(), $property->getValue()));
        }

        return Command::SUCCESS;
    }
}

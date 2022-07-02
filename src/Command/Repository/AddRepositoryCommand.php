<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Command\Repository;

use DigitalRevolution\SymfonyConsoleValidation\InputValidator;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Config\RepositoryProperty;
use DR\GitCommitNotification\Input\AddRepositoryInput;
use DR\GitCommitNotification\Repository\Config\RepositoryRepository;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('git:repository:add', 'Add a git repository to monitor commits for.')]
class AddRepositoryCommand extends Command
{
    public function __construct(private RepositoryRepository $repositoryRepository, private InputValidator $inputValidator, ?string $name = null)
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

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $validatedInput = $this->inputValidator->validate($input, AddRepositoryInput::class);

        $repository = new Repository();
        $repository->setUrl($validatedInput->getRepository());

        // determine name
        $name = $validatedInput->getName();
        if ($name === null) {
            $output->writeln('<error>Unable to determine the name of the repository. Specify repository name with the --name flag</error>');

            return Command::FAILURE;
        }
        if ($this->repositoryRepository->findOneBy(['name' => $name]) !== null) {
            $output->writeln(sprintf('<error>A repository with name `%s` already exists.</error>', $name));

            return Command::FAILURE;
        }

        $repository->setName($name);

        // set upsource project id
        if ($validatedInput->getUpsourceId() !== null) {
            $repository->addRepositoryProperty(new RepositoryProperty('upsource-project-id', $validatedInput->getUpsourceId()));
        }

        // set gitlab project id
        if ($validatedInput->getGitlabId() !== null) {
            $repository->addRepositoryProperty(new RepositoryProperty('gitlab-project-id', (string)$validatedInput->getGitlabId()));
        }

        // save
        $this->repositoryRepository->add($repository, true);

        $output->writeln('<info>Successfully added repository: ' . $repository->getName() . '</info>');
        foreach ($repository->getRepositoryProperties() as $property) {
            $output->writeln(sprintf('<info>- property: %s: %s</info>', $property->getName(), $property->getValue()));
        }

        return Command::SUCCESS;
    }
}

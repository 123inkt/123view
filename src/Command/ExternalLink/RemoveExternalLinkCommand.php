<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Command\ExternalLink;

use DR\GitCommitNotification\Repository\Config\ExternalLinkRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('external-link:remove', 'Remove an external link by id.')]
class RemoveExternalLinkCommand extends Command
{
    public function __construct(private ExternalLinkRepository $linkRepository, ?string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::REQUIRED, 'The id of the external link.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id   = $input->getArgument('id');
        $link = $this->linkRepository->find($id);
        if ($link === null) {
            $output->writeln('<error>No external link found by id: ' . $id);

            return Command::FAILURE;
        }

        $this->linkRepository->remove($link, true);

        $output->writeln(sprintf('<info>Successfully removed the link: %s - %s</info>', $link->getPattern(), $link->getUrl()));

        return Command::SUCCESS;
    }
}

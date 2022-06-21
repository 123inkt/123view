<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Command\ExternalLink;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Entity\ExternalLink;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('external-link:list', 'List all currently configured external links')]
class ListExternalLinksCommand extends Command
{
    public function __construct(private ManagerRegistry $doctrine, ?string $name = null)
    {
        parent::__construct($name);
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $links = $this->doctrine->getRepository(ExternalLink::class)->findAll();
        if (count($links) === 0) {
            $output->writeln('<info>No existing external links found.</info>');

            return Command::SUCCESS;
        }

        $output->writeln('External links: ');
        foreach ($links as $link) {
            $output->writeln(sprintf('- [%s] %s - %s', $link->getId(), $link->getPattern(), $link->getUrl()));
        }

        return Command::SUCCESS;
    }
}

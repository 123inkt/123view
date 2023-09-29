<?php
declare(strict_types=1);

namespace DR\Review\Command\Revision;

use DR\Review\Message\Revision\ValidateRevisionsMessage;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Utils\Assert;
use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand('revisions:validate', 'Validate currently stored hashes with remote hashes, adding and removing any differences')]
class ValidateRevisionsCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly RepositoryRepository $repositoryRepository, private readonly MessageBusInterface $bus)
    {
        parent::__construct();
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->repositoryRepository->findByValidateRevisions() as $repository) {
            $this->bus->dispatch(new ValidateRevisionsMessage(Assert::notNull($repository->getId())));
        }

        return self::SUCCESS;
    }
}

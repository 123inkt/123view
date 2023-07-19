<?php
declare(strict_types=1);

namespace DR\Review\Command\Revision;

use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Service\Git\GarbageCollect\LockableGitGarbageCollectService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand('git:garbage-collect', "Run git garbage collect on all repositories")]
class GarbageCollectGitCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly RepositoryRepository $repositoryRepository,
        private readonly LockableGitGarbageCollectService $garbageCollectService
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->repositoryRepository->findBy(['active' => true]) as $repository) {
            try {
                $this->logger?->info('Starting garbage collect for repository {name}.', ['name' => $repository->getName()]);
                $this->garbageCollectService->garbageCollect($repository, 'now');
                $this->logger?->info('Garbage collect repository {name} completed.', ['name' => $repository->getName()]);
            } catch (Throwable $e) {
                $this->logger?->error(
                    'Failed to garbage collect repository {name}. {message}',
                    ['name' => $repository->getName(), 'message' => $e->getMessage(), 'exception' => $e]
                );
                continue;
            }
        }

        return Command::SUCCESS;
    }
}

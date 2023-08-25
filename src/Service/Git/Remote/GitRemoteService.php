<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Remote;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Repository\RepositoryCredential;
use DR\Review\Entity\Repository\RepositoryUtil;
use DR\Review\Exception\RepositoryException;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class GitRemoteService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly RepositoryRepository $repositoryRepository,
        private readonly CacheableGitRepositoryService $repositoryService,
        private readonly GitCommandBuilderFactory $commandFactory,
    ) {
    }

    public function updateRemoteUrls(RepositoryCredential $credential): void
    {
        $repositories = $this->repositoryRepository->findBy(['credential' => $credential]);
        foreach ($repositories as $repository) {
            try {
                $this->updateRemoteUrl($repository);
            } catch (RepositoryException $exception) {
                $this->logger?->error($exception->getMessage());
            }
        }
    }

    /**
     * @throws RepositoryException
     */
    public function updateRemoteUrl(Repository $repository): void
    {
        $uri = RepositoryUtil::getUriWithCredentials($repository);

        $commandBuilder = $this->commandFactory->createRemote()->setUrl('origin', (string)$uri);

        // set new remote url
        $output = $this->repositoryService->getRepository($repository)->execute($commandBuilder);

        $this->logger?->info($output);
    }
}

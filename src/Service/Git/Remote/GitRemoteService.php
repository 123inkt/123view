<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Remote;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Repository\RepositoryUtil;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class GitRemoteService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly CacheableGitRepositoryService $repositoryService,
        private readonly GitCommandBuilderFactory $commandFactory
    ) {
    }

    /**
     * @throws RepositoryException
     */
    public function updateRemoteUrl(Repository $repository): void
    {
        $uri = RepositoryUtil::getUriWithCredentials($repository);

        $commandBuilder = $this->commandFactory->createRemote()->setUrl('origin', (string)$uri);

        // set new remote url
        $this->repositoryService->getRepository($repository)->execute($commandBuilder);
    }
}

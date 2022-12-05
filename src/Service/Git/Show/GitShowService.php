<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Show;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\Revision;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\GitRepositoryService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class GitShowService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly GitCommandBuilderFactory $builderFactory, private readonly GitRepositoryService $repositoryService)
    {
    }

    /**
     * @throws RepositoryException
     */
    public function getFileAtRevision(Revision $revision, string $filePath): string
    {
        /** @var Repository $repository */
        $repository     = $revision->getRepository();
        $commandBuilder = $this->builderFactory->createShow()->file((string)$revision->getCommitHash(), $filePath);

        $this->logger?->debug(sprintf('Executing `%s` for `%s`', $commandBuilder, $repository->getName()));

        return $this->repositoryService->getRepository((string)$repository->getUrl())->execute($commandBuilder);
    }
}

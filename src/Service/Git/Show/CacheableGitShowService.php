<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Show;

use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Utility\Assert;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Contracts\Cache\CacheInterface;

class CacheableGitShowService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly CacheInterface $revisionCache, private readonly GitShowService $showService)
    {
    }

    /**
     * @throws RepositoryException|InvalidArgumentException
     */
    public function getFileAtRevision(Revision $revision, string $filePath): string
    {
        $key = sprintf('%d-%s-%s', Assert::notNull($revision->getRepository())->getId(), $revision->getCommitHash(), $filePath);

        return $this->revisionCache->get($key, fn() => $this->showService->getFileAtRevision($revision, $filePath));
    }
}

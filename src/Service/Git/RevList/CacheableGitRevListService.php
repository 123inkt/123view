<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\RevList;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;

class CacheableGitRevListService
{
    public function __construct(private readonly CacheInterface $cache, private readonly LockableGitRevListService $revListService)
    {
    }

    /**
     * @return string[]
     * @throws InvalidArgumentException|RepositoryException
     */
    public function getCommitsAheadOfMaster(Repository $repository, string $branchName): array
    {
        $cacheKey = hash('sha256', sprintf('%s-%s', $repository->getId(), $branchName));

        /** @var string[] $result */
        $result = $this->cache->get(
            $cacheKey,
            function (CacheItemInterface $item) use ($repository, $branchName) {
                $startTime = microtime(true);
                $branches  = $this->revListService->getCommitsAheadOfMaster($repository, $branchName);
                $duration  = (int)(microtime(true) - $startTime);

                // set the cache duration 5 times the duration of git command with minimum of 60 seconds and maximum of 3 minutes
                $item->expiresAfter(min(180, max(60, $duration * 4)));

                return $branches;
            }
        );

        return $result;
    }
}

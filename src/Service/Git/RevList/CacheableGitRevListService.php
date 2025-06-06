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
    private const MIN_CACHE_TIME = 60;
    private const MAX_CACHE_TIME = 180;

    public function __construct(private readonly CacheInterface $cache, private readonly LockableGitRevListService $revListService)
    {
    }

    /**
     * @return string[]
     * @throws InvalidArgumentException|RepositoryException
     */
    public function getCommitsAheadOf(Repository $repository, string $branchName, ?string $targetBranch = null): array
    {
        $cacheKey = 'git-rev-list-' . hash('sha256', sprintf('%s-%s-%S', $repository->getId(), $branchName, $targetBranch));

        /** @var string[] $result */
        $result = $this->cache->get(
            $cacheKey,
            function (CacheItemInterface $item) use ($repository, $branchName, $targetBranch) {
                $startTime = microtime(true);
                $branches  = $this->revListService->getCommitsAheadOf($repository, $branchName, $targetBranch);
                $duration  = (int)(microtime(true) - $startTime);

                // set the cache duration 5 times the duration of git command with minimum of 60 seconds and maximum of 3 minutes
                $item->expiresAfter(min(self::MAX_CACHE_TIME, max(self::MIN_CACHE_TIME, $duration * 5)));

                return $branches;
            }
        );

        return $result;
    }
}

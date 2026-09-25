<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Branch;

use DR\Review\Entity\Repository\Repository;
use Psr\Cache\CacheItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Throwable;

class CacheableGitBranchService
{
    public function __construct(private readonly CacheInterface $cache, private readonly GitBranchService $branchService)
    {
    }

    /**
     * @return string[]
     * @throws Throwable
     */
    public function getRemoteBranches(Repository $repository, bool $mergedOnly = false): array
    {
        $cacheKey = 'git-branch-command-' . $repository->getId() . '-' . ($mergedOnly ? 'merged' : 'all');

        return $this->cache->get($cacheKey, function (CacheItemInterface $item) use ($repository, $mergedOnly) {
            // set the cache item to expire after 60 seconds
            $item->expiresAfter(60);

            return $this->branchService->getRemoteBranches($repository, $mergedOnly);
        });
    }
}

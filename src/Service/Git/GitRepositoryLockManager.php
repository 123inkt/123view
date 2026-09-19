<?php
declare(strict_types=1);

namespace DR\Review\Service\Git;

use DR\Review\Entity\Repository\Repository;
use DR\Utils\Assert;
use Symfony\Component\Filesystem\Filesystem;

class GitRepositoryLockManager
{
    private string $cacheDirectory;

    /** @var array<int, true> repository IDs that are currently locked by this process */
    private array $activeLocks = [];

    public function __construct(string $cacheDirectory, private readonly Filesystem $filesystem)
    {
        $this->cacheDirectory = $cacheDirectory;
    }

    /**
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return T
     */
    public function start(Repository $repository, callable $callback): mixed
    {
        $lockfile = sprintf('%s%s.%s.lock', $this->cacheDirectory, $repository->getId(), $repository->getName());
        if (is_dir(dirname($lockfile)) === false) {
            $this->filesystem->mkdir(dirname($lockfile));
        }

        $fileHandle = Assert::notFalse(fopen($lockfile, "wb+"));

        $repositoryId = $repository->getId();

        try {
            flock($fileHandle, LOCK_EX);
            $this->activeLocks[$repositoryId] = true;

            return $callback();
        } finally {
            unset($this->activeLocks[$repositoryId]);
            flock($fileHandle, LOCK_UN);
        }
    }

    /**
     * Returns true when the given repository's lock is currently held by this process instance.
     * Use to assert preconditions in code that must run within a lock.
     */
    public function lockAcquired(Repository $repository): bool
    {
        return isset($this->activeLocks[$repository->getId()]);
    }
}
